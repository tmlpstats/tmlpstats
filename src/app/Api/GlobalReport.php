<?php
namespace TmlpStats\Api;

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

        $centerData = $this->getWeekScoreboardByCenter($report, $region, $report->reportingDate);
        $weeklyData = $this->getWeekScoreboard($report, $region, $report->reportingDate);

        $a = new Arrangements\RegionByRating($centerData);
        $data = $a->compose();

        $data['summary']['points'] = $weeklyData->points();
        $data['summary']['rating'] = $weeklyData->rating();

        $this->putCache($data);

        return $data;
    }

    public function getQuarterScoreboard(Models\GlobalReport $report, Models\Region $region)
    {
        $cached = $this->checkCache(compact('report', 'region'));
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboardByCenter($report->reportingDate, $region);
        $centerCount = count($scoreboardData);

        $gamesData = [];
        foreach ($scoreboardData as $center => $centerData) {
            foreach ($centerData->sortedValues() as $week) {
                $date = $week->week->toDateString();
                $data = array_get($gamesData, "{$date}.games", []);

                foreach (Domain\Scoreboard::GAME_KEYS as $game) {
                    if (!isset($data[$game])) {
                        $data[$game]['promise'] = $data[$game]['actual'] = 0;
                    }
                    $data[$game]['promise'] += $week->game($game)->promise();
                    $data[$game]['actual'] += $week->game($game)->actual();
                }
                $gamesData[$date]['games'] = $data;
            }
        }

        foreach ($gamesData as $date => &$week) {
            $week['games']['gitw']['promise'] = round($week['games']['gitw']['promise'] / $centerCount);
            $week['games']['gitw']['actual'] = round($week['games']['gitw']['actual'] / $centerCount);
        }

        $this->putCache($gamesData);

        return Domain\ScoreboardMultiWeek::fromArray($gamesData);
    }

    public function getQuarterScoreboardByCenter(Carbon $reportingDate, Models\Region $region)
    {
        return $this->context
                    ->getEncapsulation(Domain\RegionScoreboard::class, compact('region', 'reportingDate'))
                    ->getScoreboard();
    }

    public function getWeekScoreboard(Models\GlobalReport $report, Models\Region $region, Carbon $futureDate = null)
    {
        $cached = $this->checkCache(compact('report', 'region', 'futureDate'));
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboard($report, $region);

        $date = $futureDate ?: $report->reportingDate;

        $reportData = $scoreboardData->getWeek($date) ?? [];

        $this->putCache($reportData);

        return $reportData;
    }

    public function getWeekScoreboardByCenter(Models\GlobalReport $report, Models\Region $region, Carbon $futureDate = null)
    {
        $date = $futureDate ?: $report->reportingDate;

        $data = $this->getQuarterScoreboardByCenter($report->reportingDate, $region);

        $output = [];
        foreach ($data as $center => $centerData) {
            $output[$center] = $centerData->getWeek($date);
        }

        return $output;
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

    public function getStatsReports(Models\GlobalReport $report, Models\Region $region)
    {
        return $report->statsReports()
                      ->validated()
                      ->byRegion($region)
                      ->with('center')
                      ->get()
                      ->keyBy(function($report) {
                            return $report->center->name;
                      });
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
