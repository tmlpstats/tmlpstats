<?php namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple methods
// that take typed input and return array responses.
use App;
use Cache;
use Carbon\Carbon;
use Illuminate\View\View;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
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
        $cached = $this->checkCache($this->merge(compact('report', 'region'), $options));
        if ($cached) {
            return $cached;
        }

        $date = $report->reportingDate;
        if (isset($options['date']) && ($options['date'] instanceof Carbon || is_string($options['date']))) {
            $date = is_string($options['date']) ? Carbon::parse($options['date']) : $options['date'];
        }

        $includeOriginalPromise = isset($options['includeOriginalPromise']) ? (bool) $options['includeOriginalPromise'] : false;

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

        $this->putCache($reportData);

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

        $ttl = Controllers\ReportDispatchAbstractController::CACHE_TTL;

        $output = [];
        $ckBase = "{$report->id}{$region->id}";
        $controller = App::make(Controllers\GlobalReportController::class);
        foreach ($pages as $page) {
            // Yes I know, I've re-invented caching, but I'd rather do it here than tie into ReportDispatchAbstractController while we're working on this separately.
            $f = function () use ($page, $report, $region, $controller) {
                $response = $controller->newDispatch($page, $report, $region);
                if ($response instanceof View) {
                    $response = $response->render();
                }

                return $response;
            };
            if ($controller->useCache($page)) {
                $response = Cache::tags(['reports'])->remember("{$ckBase}.{$page}", $ttl, $f);
            } else {
                $response = $f();
            }

            $output[$page] = $response;
        }

        return ['pages' => $output];
    }

    public function getReportPagesByDate(Models\Region $region, Carbon $reportingDate, $pages)
    {
        $report = Models\GlobalReport::reportingDate($reportingDate)->firstOrFail();

        return $this->getReportPages($report, $region, $pages);
    }
}
