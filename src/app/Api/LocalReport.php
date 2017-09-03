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

        $region = $statsReport->center->region->getParentGlobalRegion();
        $globalScoreboard = App::make(GlobalReport::class)->getQuarterScoreboardByCenter($statsReport->reportingDate, $region);

        return array_get($globalScoreboard, $statsReport->center->name);
    }

    public function getWeekScoreboard(Models\StatsReport $statsReport)
    {
        $cached = $this->checkCache(compact('statsReport'));
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboard($statsReport);

        $response = $scoreboardData->getWeek($statsReport->reportingDate) ?? [];

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
            ->where('version', 'api')
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
                'reconcile' => $reportingDate->gte($cq->classroom3Date) && $this->context->can('reconcile', $globalRegion),
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
