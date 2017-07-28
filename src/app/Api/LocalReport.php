<?php
namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple static methods
// that take typed input and return array responses.
use App;
use Cache;
use Carbon\Carbon;
use Illuminate\View\View;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;
use TmlpStats\Http\Controllers;
use TmlpStats\Reports\Arrangements;

class LocalReport extends AuthenticatedApiBase
{
    public function getQuarterScoreboard(Models\StatsReport $statsReport, $options = [])
    {
        $cached = $this->checkCache($this->merge(compact('statsReport'), $options));
        if ($cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;
        $returnUnflattened = isset($options['returnUnflattened']) ? (bool) $options['returnUnflattened'] : false;
        $returnObject = array_get($options, 'returnObject', false);
        $includeOriginalPromise = isset($options['includeOriginalPromise']) ? (bool) $options['includeOriginalPromise'] : false;

        $scoreboardData = [];

        $originalPromise = null;

        $quarterStartDate = $statsReport->quarter->getQuarterStartDate($statsReport->center);

        // XXX We have many places making use of this function.
        // Rather than changing behavior for all, let's make the 'correct' behavior an option.
        // Then we can go through report by report and see which need the fix and which need the old behavior.
        if (array_get($options, 'back_from_this_week', false)) {
            $week = $statsReport->reportingDate->copy();
        } else {
            $week = $statsReport->quarter->getQuarterEndDate($statsReport->center)->copy();
        }
        while ($week->gt($quarterStartDate)) {

            // Walk backwards through the quarter week by week and collect the promise/actual objects
            // from each official report as we find them
            // Take the newest version of each
            $report = Models\StatsReport::byCenter($statsReport->center)
                ->reportingDate($week)
                ->official()
                ->first();
            if ($report) {
                $weekData = $report->centerStatsData()->get();
                foreach ($weekData as $data) {

                    $dateStr = $data->reportingDate->toDateString();
                    if (!isset($scoreboardData[$dateStr][$data->type])) {
                        if ($data->type == 'promise' || $data->reportingDate->lte($statsReport->reportingDate)) {
                            $scoreboardData[$dateStr][$data->type] = $data;
                        }
                    }

                    // Keep searching until we find the very first promise
                    if ($includeOriginalPromise && $data->type == 'promise') {
                        $originalData = clone $data;
                        $originalData->type = 'original';
                        $scoreboardData[$dateStr]['original'] = $originalData;
                    }
                }
            }

            $week = $week->copy()->subWeek(); // Wasn't strictly necessary, but let's be safe with mutable dates
        }

        if ($returnUnprocessed) {
            if ($returnUnflattened) {
                $response = $scoreboardData;
            } else {
                $response = array_flatten($scoreboardData);
            }

            $this->putCache($response);

            return $response;
        }

        $a = new Arrangements\GamesByWeek();
        $weeks = $a->buildObject(array_flatten($scoreboardData));
        if ($returnObject) {
            return $weeks;
        } else {
            $weeksArray = $weeks->toArray();
            $this->putCache($weeksArray);

            return $weeksArray;
        }
    }

    public function getWeekScoreboard(Models\StatsReport $statsReport)
    {
        $cached = $this->checkCache(compact('statsReport'));
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboard($statsReport);

        $dateStr = $statsReport->reportingDate->toDateString();

        $response = [];
        if (isset($scoreboardData[$dateStr])) {
            $response = $scoreboardData[$dateStr];
        }

        $this->putCache($response);

        return $response;
    }

    public function getApplicationsList(Models\StatsReport $statsReport, $options = [])
    {
        $cached = $this->checkCache($this->merge(compact('statsReport'), $options));
        if ($cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;

        $registrations = Models\TmlpRegistrationData::byStatsReport($statsReport)
            ->with('registration.person', 'committedTeamMember.person')
            ->get();
        if (!$registrations) {
            return [];
        } else if ($returnUnprocessed) {
            $this->putCache($registrations);

            return $registrations;
        }

        $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
            'registrationsData' => $registrations,
            'quarter' => $statsReport->quarter,
        ]);
        $data = $a->compose();

        $this->putCache($data['reportData']);

        return $data['reportData'];
    }

    // TODO decide if we want this to be a public API function. Right now it's not exactly API-valid
    public function getClassList(Models\StatsReport $statsReport)
    {
        $teamMembers = Models\TeamMemberData::byStatsReport($statsReport)->with('teamMember.person')->get();
        if (!$teamMembers) {
            return [];
        }

        return $teamMembers;
    }

    public function getClassListByQuarter(Models\StatsReport $statsReport)
    {
        $teamMembers = self::getClassList($statsReport);

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMembers]);
        $data = $a->compose();

        return $data['reportData'];
    }

    public function getCourseList(Models\StatsReport $statsReport)
    {
        $courses = [];
        $courseData = Models\CourseData::byStatsReport($statsReport)->with('course')->get();
        foreach ($courseData as $data) {
            $courses[] = $data;
        }

        return $courses;
    }

    public function getLastStatsReportSince(Models\Center $center, Carbon $reportingDate, $flags = [])
    {
        $quarter = static::quarterForCenterDate($center, $reportingDate);

        $query = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->where('reporting_date', '<', $reportingDate)
            ->orderBy('reporting_date', 'desc');

        if (in_array('official', $flags)) {
            $query = $query->official();
        }

        return $query->first();
    }

    public static function ensureStatsReport(Models\Center $center, Carbon $reportingDate, $requireNew = true)
    {
        $quarter = static::quarterForCenterDate($center, $reportingDate);

        $query = Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->reportingDate($reportingDate)
            ->orderBy('id', 'desc');
        if ($requireNew) {
            $query->submitted(false);
        }
        $report = $query->first();

        if (!$report) {
            $report = Models\StatsReport::create([
                'center_id' => $center->id,
                'quarter_id' => $quarter->id,
                'reporting_date' => $reportingDate->toDateTimeString(),
                'version' => 'api',
            ]);
        }

        return $report;
    }

    public function getCenterQuarter(Models\Center $center, Models\Quarter $quarter)
    {
        $quarter->setRegion($center->region);

        return Domain\CenterQuarter::ensure($center, $quarter);
    }

    public function reportViewOptions(Models\Center $center, Carbon $reportingDate)
    {
        $crd = Encapsulations\CenterReportingDate::ensure($center, $reportingDate);
        $cq = $crd->getCenterQuarter();
        $statsReport = static::getOfficialReport($center, $reportingDate);
        $globalRegion = $center->region->getParentGlobalRegion();
        $canReportToken = $this->context->can('readLink', Models\ReportToken::class);
        $reportToken = null;
        if ($canReportToken) {
            $globalReport = $statsReport->globalReports->last();
            $reportToken = Models\ReportToken::get($globalReport, $center);
        }

        return [
            'statsReportId' => $statsReport->id,
            'globalRegionId' => $globalRegion->abbrLower(),
            'flags' => [
                'canReadContactInfo' => $this->context->can('readContactInfo', $statsReport),
                'firstWeek' => $cq->firstWeekDate->toDateString() == $reportingDate->toDateString(),
                'nextQtrAccountabilities' => $crd->canShowNextQtrAccountabilities(),
            ],
            'centerInfo' => collect($center->toArray())->only(['id', 'name', 'abbreviation', 'teamName', 'regionId']),
            'reportToken' => ($reportToken !== null) ? $reportToken->getUrl() : null,
            'capabilities' => [
                'reportToken' => $canReportToken,
                'reportNavLinks' => $this->context->can('showReportNavLinks', Models\StatsReport::class),
            ],
        ];
    }

    public function getReportPages(Models\Center $center, Carbon $reportingDate, $pages)
    {
        $report = static::getOfficialReport($center, $reportingDate);
        // Unsure if these are all needed, but we're going to do them, for king and country.
        $this->context->setCenter($center);
        $this->context->setReportingDate($reportingDate);
        $this->context->setDateSelectAction('ReportsController@getCenterReport', ['abbr' => $center->abbrLower()]);
        $this->assertCan('read', $report);

        $output = [];
        $ckBase = "{$report->id}{$center->id}";
        $controller = App::make(Controllers\StatsReportController::class);
        foreach ($pages as $page) {
            $f = function () use ($page, $report, $center, $controller) {
                if (!$report->isValidated() && $page != 'Overview') {
                    return '<p>This report did not pass validation. See Report Details for more information.</p>';
                }

                $response = $controller->newDispatch($page, $report, $center);
                if ($response instanceof View) {
                    $response = $response->render();
                }

                return $response;
            };
            if ($ttl = $controller->getPageCacheTime($page)) {
                $response = Cache::tags(['reports', "statsReport{$report->id}"])->remember("{$ckBase}.{$page}", $ttl, $f);
            } else {
                $response = $f();
            }

            $output[$page] = $response;
        }

        return ['pages' => $output];
    }

    public static function getOfficialReport(Models\Center $center, Carbon $reportingDate)
    {
        return Models\StatsReport::byCenter($center)
            ->reportingDate($reportingDate)
            ->official()
            ->firstOrFail();
    }

    public static function quarterForCenterDate(Models\Center $center, Carbon $reportingDate)
    {
        $quarter = Models\Quarter::getQuarterByDate($reportingDate, $center->region);
        if (!$quarter) {
            throw new ApiExceptions\BadRequestException('Unable to find quarter which is required for fetching this data');
        }

        return $quarter;
    }

    public static function getReportingDate(Models\Center $center)
    {
        return $reportingDate = Carbon::parse('this friday', $center->timezone)->startOfDay();
    }
}
