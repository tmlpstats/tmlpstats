<?php namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple methods
// that take typed input and return array responses.
use App;
use Cache;
use Carbon\Carbon;
use Illuminate\View\View;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;
use TmlpStats\Http\Controllers;
use TmlpStats\Reports\Arrangements;

class GlobalReport extends AuthenticatedApiBase
{
    public function getRating(Models\GlobalReport $report, Models\Region $region)
    {
        $cached = $this->checkCache(compact('report', 'region'));
        if ($cached) {
            return $cached;
        }

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return null;
        }

        $weeklyData = $this->getQuarterScoreboard($report, $region);

        $a = new Arrangements\RegionByRating($statsReports);
        $data = $a->compose();

        $dateString = $report->reportingDate->toDateString();
        $data['summary']['points'] = $weeklyData[$dateString]['points']['total'];
        $data['summary']['rating'] = $weeklyData[$dateString]['rating'];

        $this->putCache($data);

        return $data;
    }

    public function getQuarterScoreboard(Models\GlobalReport $report, Models\Region $region)
    {
        $cached = $this->checkCache(compact('report', 'region'));
        if ($cached) {
            return $cached;
        }

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return [];
        }

        $cumulativeData = [];
        foreach ($statsReports as $statsReport) {
            $centerStatsData = App::make(LocalReport::class)->getQuarterScoreboard($statsReport);

            foreach ($centerStatsData as $dateStr => $week) {
                foreach (['promise', 'actual'] as $type) {

                    // Skip if we don't have that type, or if the data is empty
                    if (!isset($week[$type]) || $week[$type]['cap'] === null) {
                        continue;
                    }

                    $data = $week[$type];

                    if (isset($cumulativeData[$dateStr][$type])) {
                        $weekData = $cumulativeData[$dateStr][$type];
                    } else {
                        $weekData = new \stdClass();
                        $weekData->type = $type;
                        $weekData->reportingDate = Carbon::parse($dateStr);
                    }

                    foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game) {

                        if (!isset($weekData->$game)) {
                            $weekData->$game = 0;
                        }
                        $weekData->$game += $data[$game];
                    }
                    $cumulativeData[$dateStr][$type] = $weekData;
                }
            }
        }

        $scoreboardData = [];
        $count = count($statsReports);
        foreach ($cumulativeData as $date => $week) {
            foreach ($week as $type => $data) {
                // GITW is calculated as an average, so we need the total first
                $total = $data->gitw;
                $data->gitw = ($total / $count);

                $scoreboardData[] = $data;
            }
        }

        $a = new Arrangements\GamesByWeek($scoreboardData);
        $weeklyData = $a->compose();

        $this->putCache($weeklyData['reportData']);

        return $weeklyData['reportData'];
    }

    public function getQuarterScoreboardByCenter(Models\GlobalReport $report, Models\Region $region)
    {
        $reportingDate = $report->reportingDate;
        $rrd = Encapsulations\RegionReportingDate::ensure($region, $reportingDate);
        $rq = $rrd->getRegionQuarter();

        $globalReports = Models\GlobalReport::between($rq->firstWeekDate, $reportingDate)
            ->get()
            ->keyBy(function($gr) { return $gr->reportingDate->toDateString(); });

        $centers = Models\Center::byRegion($region)
            ->get()
            ->keyBy(function($c) { return $c->id; })
            ->map(function($c) { return $c->name; });

        $csdByCenter = [];

        // First, collect all of the centerStats data objects
        for ($targetDate = $rq->firstWeekDate; $targetDate->lte($reportingDate); $targetDate = $targetDate->copy()->addWeek()) {
            $gr = $globalReports->get($targetDate->toDateString());

            $statsReports = $gr->statsReports()
                               ->byRegion($region)
                               ->validated()
                               ->get()
                               ->keyBy(function($report) { return $report->id; });

            $csds = Models\CenterStatsData::whereIn('stats_report_id', $statsReports->keys())
                                          ->get();
            foreach ($csds as $csd) {
                $centerId = $statsReports->get($csd->statsReportId)->centerId;
                $csdByCenter[$centers[$centerId]][] = $csd;
            }
        }
        ksort($csdByCenter);

        // Then, format and filter data
        $output = [];
        foreach($csdByCenter as $center => $items) {
            // filter out duplicates from csdByCenter, keeping the latest promise/actual for each
            // keyBy will end up keeping the last reported thing with the same key... we use that as an easy dedup: https://laravel.com/docs/5.2/collections#method-keyby
            $csdByCenter[$center] = collect($items)
                ->keyBy(function($csd) { return "{$csd->type} " . $csd->reportingDate->toDateString(); })
                ->sortBy(function($csd) { return $csd->reportingDate->toDateString(); }); // re-sort by reportingDate in case later promises took over older ones

            // re-key to be consumed by ScoreboardMultiWeek
            $centerData = [];
            foreach ($items as $csd) {
                $centerData[$csd->reportingDate->toDateString()][$csd->type] = $csd;
            }

            // hydrate ScoreboardMultiWeek
            $output[$center] = Domain\ScoreboardMultiWeek::fromArray($centerData)->toArray();
        }

        return $output;
    }

    public function getWeekScoreboard(Models\GlobalReport $report, Models\Region $region, Carbon $futureDate = null)
    {
        $cached = $this->checkCache(compact('report', 'region', 'futureDate'));
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboard($report, $region);

        $dateStr = $futureDate ? $futureDate->toDateString() : $report->reportingDate->toDateString();

        $reportData = [];
        if (isset($scoreboardData[$dateStr])) {
            $reportData = $scoreboardData[$dateStr];
        }

        $this->putCache($reportData);

        return $reportData;
    }

    public function getWeekScoreboardByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $date = array_get($options, 'date', $report->reportingDate);
        $includeOriginalPromise = array_get($options, 'includeOriginalPromise', false);

        // Use optimized method of getting scoreboards
        // This currently doesn't support including original promises
        if (!$includeOriginalPromise) {
            $data = $this->getQuarterScoreboardByCenter($report, $region);

            $output = [];
            foreach ($data as $center => $centerData) {
                $output[$center] = $centerData[$date->toDateString()];
            }

            return $output;
        }

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return [];
        }

        $dateStr = $date->toDateString();

        $reportData = [];
        foreach ($statsReports as $statsReport) {
            $centerStatsData = App::make(LocalReport::class)->getQuarterScoreboard($statsReport, compact('includeOriginalPromise'));

            $centerName = $statsReport->center->name;

            $reportData[$centerName] = $centerStatsData[$dateStr];
        }

        return $reportData;
    }

    public function getApplicationsListByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $cached = $this->checkCache($this->merge(compact('report', 'region'), $options));
        if ($cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return [];
        }

        $registrations = [];
        foreach ($statsReports as $statsReport) {

            $reportRegistrations = App::make(LocalReport::class)->getApplicationsList($statsReport, [
                'returnUnprocessed' => $returnUnprocessed,
            ]);

            foreach ($reportRegistrations as $registration) {
                $registrations[] = $registration;
            }
        }

        $this->putCache($registrations);

        return $registrations;
    }

    public function getClassListByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $statsReports = $this->getStatsReports($report, $region);

        $teamMembers = [];
        foreach ($statsReports as $report) {

            $reportTeamMembers = App::make(LocalReport::class)->getClassList($report);
            foreach ($reportTeamMembers as $member) {
                $teamMembers[] = $member;
            }
        }

        return $teamMembers;
    }

    public function getCourseList(Models\GlobalReport $report, Models\Region $region)
    {
        $statsReports = $this->getStatsReports($report, $region);

        $courses = [];
        foreach ($statsReports as $statsReport) {
            $reportCourses = App::make(LocalReport::class)->getCourseList($statsReport);
            foreach ($reportCourses as $course) {
                $courses[] = $course;
            }
        }

        return $courses;
    }

    protected function getStatsReports(Models\GlobalReport $report, Models\Region $region)
    {
        return $report->statsReports()
                      ->validated()
                      ->byRegion($region)
                      ->get();
    }

    public function getReportPages(Models\GlobalReport $report, Models\Region $region, $pages)
    {
        // Unsure if these are all needed, but we're going to do them, for king and country.
        $this->context->setRegion($region);
        $this->context->setReportingDate($report->reportingDate);
        $this->context->setDateSelectAction('ReportsController@getRegionReport', ['abbr' => $region->abbrLower()]);
        $this->assertCan('read', $report);

        $output = [];
        $ckBase = "{$report->id}{$region->id}";
        $controller = App::make(Controllers\GlobalReportController::class);
        $begin = time();
        foreach ($pages as $page) {
            $f = function () use ($page, $report, $region, $controller) {
                $response = $controller->newDispatch($page, $report, $region);
                if ($response instanceof View) {
                    $response = $response->render();
                }

                return $response;
            };
            if ($ttl = $controller->getPageCacheTime($page)) {
                $response = Cache::tags(['reports', "globalReport{$report->id}"])->remember("{$ckBase}.{$page}", $ttl, $f);
            } else {
                $response = $f();
            }

            $output[$page] = $response;
            // If we exceed 8 seconds, return what we have so far to the user.
            // This allows better responsiveness to tab switching.
            if ((time() - $begin) >= 8) {
                break;
            }
        }

        return ['pages' => $output];
    }

    public function getReportPagesByDate(Models\Region $region, Carbon $reportingDate, $pages)
    {
        $report = Models\GlobalReport::reportingDate($reportingDate)->firstOrFail();

        return $this->getReportPages($report, $region, $pages);
    }

    public function reportViewOptions(Models\Region $region, Carbon $reportingDate)
    {
        $report = Models\GlobalReport::reportingDate($reportingDate)->firstOrFail();
        $this->assertCan('read', $report);

        $rrd = Encapsulations\RegionReportingDate::ensure($region, $reportingDate);
        $rq = $rrd->getRegionQuarter();

        return [
            'globalReportId' => $report->id,
            'flags' => [
                'afterClassroom2' => $reportingDate->gte($rq->classroom2Date),
                'lastWeek' => $reportingDate->gte($rq->endWeekendDate),
            ],
            'capabilities' => [
                '_ignoreMe' => false,
            ],
        ];
    }
}
