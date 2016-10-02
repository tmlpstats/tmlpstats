<?php
namespace TmlpStats\Http\Controllers;

use App;
use Gate;
use Illuminate\Http\Request;
use Response;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain\Scoreboard;
use TmlpStats\Http\Controllers\Encapsulate;
use TmlpStats\Http\Controllers\Traits\GlobalReportDispatch;
use TmlpStats\Reports\Arrangements;

class GlobalReportController extends ReportDispatchAbstractController
{
    use GlobalReportDispatch;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth.token');
        $this->middleware('auth');
        $this->context = App::make(Api\Context::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize('index', Models\GlobalReport::class);

        $globalReports = Models\GlobalReport::orderBy('reporting_date', 'desc')->get();

        return view('globalreports.index', compact('globalReports'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show(Request $request, $id)
    {
        $region = $this->getRegion($request, true);
        $globalReport = Models\GlobalReport::findOrFail($id);

        return $this->showForRegion($request, $globalReport, $region);
    }

    public function showForRegion(Request $request, Models\GlobalReport $globalReport, Models\Region $region)
    {
        $this->context->setRegion($region);
        $this->context->setReportingDate($globalReport->reportingDate);
        $this->context->setDateSelectAction('ReportsController@getRegionReport', ['abbr' => $region->abbrLower()]);
        $this->authorize('read', $globalReport);

        $reportToken = Gate::allows('readLink', Models\ReportToken::class) ? Models\ReportToken::get($globalReport, $region) : null;

        $quarter = Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        $showNavCenterSelect = true;

        $vmode = $request->has('viewmode') ? $request->input('viewmode') : env('GLOBAL_REPORT_VIEW_MODE', 'html');

        switch (strtolower($vmode)) {
            case 'jq':
                $template = 'show_jquery';
                break;
            case 'html':
            default:
                $template = 'show';
        }

        return view("globalreports.{$template}", compact(
            'globalReport',
            'region',
            'reportToken',
            'quarter',
            'showNavCenterSelect'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        return redirect("/globalreports/{$id}");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update($id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    protected function getStatsReportsNotOnList(Models\GlobalReport $globalReport)
    {
        $statsReports = Models\StatsReport::reportingDate($globalReport->reportingDate)
            ->submitted()
            ->validated(true)
            ->get();

        $centers = [];
        foreach ($statsReports as $statsReport) {

            if ($statsReport->globalReports()->find($globalReport->id)
                || $globalReport->statsReports()->byCenter($statsReport->center)->count() > 0
            ) {
                continue;
            }

            $centers[$statsReport->center->abbreviation] = $statsReport->center->name;
        }
        asort($centers);

        return $centers;
    }

    public function getById($id)
    {
        return Models\GlobalReport::findOrFail($id);
    }

    public function getCacheKey($model, $report)
    {
        $keyBase = parent::getCacheKey($model, $report);

        $region = $this->context->getRegion(true);

        return $region === null ? $keyBase : "{$keyBase}:region{$region->id}";
    }

    public function getCacheTags($model, $report)
    {
        $tags = parent::getCacheTags($model, $report);

        return array_merge($tags, ["globalReport{$model->id}"]);
    }

    public function dispatchReport(Request $request, $id, $report, $regionAbbr = null)
    {
        $extra = [];
        if ($regionAbbr) {
            $region = Models\Region::abbreviation($regionAbbr)->firstOrFail();
            $this->context->setRegion($region);
            $extra['region'] = $region;
        }

        return parent::dispatchReport($request, $id, $report, $extra);
    }

    public function runDispatcher(Request $request, $globalReport, $report, $extra)
    {
        $region = array_get($extra, 'region', $this->context->getRegion(true));
        $this->context->setRegion($region);
        $this->context->setReportingDate($globalReport->reportingDate);
        $this->setReportingDate($globalReport->reportingDate);

        $response = null;
        switch ($report) {
            case 'ratingsummary':
                $response = $this->getRatingSummary($globalReport, $region);
                break;
            case 'regionsummary':
                $response = $this->getRegionSummary($globalReport, $region);
                break;
            case 'regionalstats':
                $response = $this->getRegionalStats($globalReport, $region);
                break;
            case 'gamesbycenter':
                $response = $this->getGamesByCenter($globalReport, $region);
                break;
            case 'repromisesbycenter':
                $response = $this->getRepromisesByCenter($globalReport, $region);
                break;
            case 'statsreports':
                $response = $this->getCenterStatsReports($globalReport, $region);
                break;
            case 'applicationsbystatus':
                $response = $this->getTmlpRegistrationsByStatus($globalReport, $region);
                break;
            case 'applicationsoverdue':
                $response = $this->getTmlpRegistrationsOverdue($globalReport, $region);
                break;
            case 'applicationsbycenter':
                $response = $this->getTmlpRegistrationsByCenter($globalReport, $region);
                break;
            case 'applicationsoverview':
                $response = $this->getTmlpRegistrationsOverview($globalReport, $region);
                break;
            case 'traveloverview':
                $response = $this->getTravelReport($globalReport, $region);
                break;
            case 'coursesthisweek':
            case 'coursesnextmonth':
            case 'coursesupcoming':
            case 'coursescompleted':
            case 'coursesguestgames':
            case 'coursessummary':
                $response = $this->coursesData($globalReport, $region)->getOne($report);
                break;
            case 'coursesall':
                $response = $this->coursesData($globalReport, $region)->getCoursesAllClassic($globalReport, $region);
                break;
            case 'teammemberstatuswithdrawn':
            case 'teammemberstatusctw':
            case 'teammemberstatustransfer':
            case 'potentialsdetails':
            case 'potentialsoverview':
                $response = $this->teamMembersData($globalReport, $region)->getOne($report);
                break;
            case 'teammemberstatusall':
                $response = $this->teamMembersData($globalReport, $region)->getTeamMemberStatusAllClassic();
                break;
            case 'applicationst2fromweekend':
                $response = $this->getTeam2RegisteredAtWeekend($globalReport, $region);
                break;
            case 'tdosummary':
                $response = $this->getTdoSummary($globalReport, $region);
                break;
            case 'regperparticipant':
                $response = $this->getRegPerParticipant($globalReport, $region);
                break;
            case 'gaps':
                $response = $this->getGaps($globalReport, $region);
                break;
            case 'withdrawreport':
                $response = $this->getWithdrawReport($globalReport, $region);
                break;
        }

        return $response;
    }

    protected function getRatingSummary(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $data = App::make(Api\GlobalReport::class)->getRating($globalReport, $region);

        return $data ? view('globalreports.details.ratingsummary', $data) : [];
    }

    protected function getRegionSummary(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $children = Models\Region::byParent($region)->orderby('name')->get();

        $regions = [];
        $regionsData = [];
        if ($children) {
            foreach ($children as $childRegion) {
                $regions[] = $childRegion;
                $regionsData[$childRegion->abbreviation] = App::make(Api\GlobalReport::class)
                    ->getQuarterScoreboard($globalReport, $childRegion);
            }
        }
        $regions[] = $region;
        $regionsData[$region->abbreviation] = App::make(Api\GlobalReport::class)
            ->getQuarterScoreboard($globalReport, $region);

        // This should get merged with the other reg per participant code and put in the api
        $rpp = [];
        foreach ($regions as $thisRegion) {
            $abbr = $thisRegion->abbreviation;
            $statsReports = $this->getStatsReports($globalReport, $thisRegion);
            $participantCount = 0;
            foreach ($statsReports as $report) {
                $participantCount += Models\TeamMemberData::byStatsReport($report)
                    ->active()
                    ->count();
            }

            $dateStr = $globalReport->reportingDate->toDateString();
            $data = isset($regionsData[$abbr][$dateStr]['actual']) ? $regionsData[$abbr][$dateStr]['actual'] : [];
            foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game) {
                $actual = isset($data[$game]) ? $data[$game] : 0;

                $rpp[$abbr][$game] = ($participantCount > 0) ? ($actual / $participantCount) : 0;
                $rpp[$abbr]['participantCount'] = $participantCount;
            }
        }

        return view('globalreports.details.regionsummary', compact('globalReport', 'regions', 'regionsData', 'rpp'));
    }

    protected function getGaps(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $quarter = Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region);
        $nextMilestone = $quarter->getNextMilestone($globalReport->reportingDate);

        $children = Models\Region::byParent($region)->orderby('name')->get();

        $regions = [];
        if ($children) {
            foreach ($children as $childRegion) {
                $regions[] = $childRegion;
            }
        }
        $regions[] = $region;

        $regionsData = [];
        foreach ($regions as $childRegion) {
            $regionsData[$childRegion->abbreviation] = App::make(Api\GlobalReport::class)
                ->getWeekScoreboard($globalReport, $childRegion);

            if ($nextMilestone->ne($globalReport->reportingDate)) {
                $promiseData = App::make(Api\GlobalReport::class)->getWeekScoreboard($globalReport, $childRegion, $nextMilestone);

                $scoreboard = Scoreboard::blank();

                $promises = isset($promiseData['promise']) ? $promiseData['promise'] : 0;
                $actuals = isset($regionsData[$childRegion->abbreviation]['actual']) ? $regionsData[$childRegion->abbreviation]['actual'] : 0;
                foreach ($scoreboard->games() as $game) {
                    $promise = isset($promises[$game->key]) ? $promises[$game->key] : 0;
                    $actual = isset($actuals[$game->key]) ? $actuals[$game->key] : 0;

                    $scoreboard->setValue($game->key, 'promise', $promise);
                    $scoreboard->setValue($game->key, 'actual', $actual);
                }

                $regionsData[$childRegion->abbreviation] = $scoreboard->toArray();
            }
        }

        return view('globalreports.details.gaps', compact('globalReport', 'regions', 'regionsData'));
    }

    protected function getRegionalStats(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $weeks = App::make(Api\GlobalReport::class)->getQuarterScoreboard($globalReport, $region);
        if (!$weeks) {
            return null;
        }

        $a = new Arrangements\GamesByMilestone([
            'weeks' => $weeks,
            'quarter' => Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region),
        ]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    protected function getGamesByCenter(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $reportData = App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter($globalReport, $region);
        if (!$reportData) {
            return null;
        }

        $statsReports = $this->getStatsReports($globalReport, $region);

        foreach ($statsReports as $statsReport) {
            $centerName = $statsReport->center->name;
            $reportData[$centerName]['statsReport'] = $statsReport;
        }

        $totals = [];
        foreach ($reportData as $centerName => $centerData) {
            foreach ($centerData['promise'] as $game => $gameData) {
                if (!isset($totals[$game])) {
                    $totals[$game]['promise'] = 0;
                    if (isset($centerData['actual'])) {
                        $totals[$game]['actual'] = 0;
                    }
                }

                $totals[$game]['promise'] += $centerData['promise'][$game];
                if (isset($centerData['actual'])) {
                    $totals[$game]['actual'] += $centerData['actual'][$game];
                }
            }
        }
        ksort($reportData);

        $totals['gitw']['promise'] = round($totals['gitw']['promise'] / count($reportData));
        if (isset($centerData['actual'])) {
            $totals['gitw']['actual'] = round($totals['gitw']['actual'] / count($reportData));
        }

        $includeActual = true;

        return view('globalreports.details.centergames', compact(
            'reportData',
            'totals',
            'includeActual'
        ));
    }

    protected function getRepromisesByCenter(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $quarter = Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region);
        $quarterEndDate = $quarter->getQuarterEndDate();

        $reportData = App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter($globalReport, $region, [
            'includeOriginalPromise' => true,
            'date' => $quarterEndDate,
        ]);
        if (!$reportData) {
            return null;
        }

        $statsReportsAll = $this->getStatsReports($globalReport, $region);

        foreach ($statsReportsAll as $report) {
            $centerName = $report->center->name;
            $reportData[$centerName]['statsReport'] = $report;
        }

        $totals = [];
        foreach ($reportData as $centerName => $centerData) {
            foreach ($centerData['promise'] as $game => $gameData) {
                if (!isset($totals[$game])) {
                    $totals[$game]['original'] = 0;
                    $totals[$game]['promise'] = 0;
                    $totals[$game]['delta'] = 0;
                    if (isset($centerData['actual'])) {
                        $totals[$game]['actual'] = 0;
                    }
                }

                $totals[$game]['original'] += $centerData['original'][$game];
                $totals[$game]['promise'] += $centerData['promise'][$game];
                $totals[$game]['delta'] = ($totals[$game]['promise'] - $totals[$game]['original']);
                if (isset($centerData['actual'])) {
                    $totals[$game]['actual'] += $centerData['actual'][$game];
                }
            }
        }
        ksort($reportData);

        $totals['gitw']['original'] = round($totals['gitw']['original'] / count($reportData));
        $totals['gitw']['promise'] = round($totals['gitw']['promise'] / count($reportData));
        $totals['gitw']['delta'] = ($totals['gitw']['promise'] - $totals['gitw']['original']);
        if (isset($centerData['actual'])) {
            $totals['gitw']['actual'] = round($totals['gitw']['actual'] / count($reportData));
        }

        $includeActual = $globalReport->reportingDate->eq($quarterEndDate);
        $includeOriginal = true;

        return view('globalreports.details.centergames', compact(
            'reportData',
            'totals',
            'includeActual',
            'includeOriginal'
        ));
    }

    protected function getTmlpRegistrationsByStatus(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$registrations) {
            return null;
        }

        $a = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $data = $a->compose();

        return view('globalreports.details.applicationsbystatus', [
            'reportData' => $data['reportData'],
            'reportingDate' => $globalReport->reportingDate,
        ]);
    }

    protected function getTmlpRegistrationsOverdue(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$registrations) {
            return null;
        }

        $a = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $statusData = $a->compose();

        $a = new Arrangements\TmlpRegistrationsByOverdue(['registrationsData' => $statusData['reportData']]);
        $data = $a->compose();

        return view('globalreports.details.applicationsoverdue', [
            'reportData' => $data['reportData'],
            'reportingDate' => $globalReport->reportingDate,
        ]);
    }

    protected function getTmlpRegistrationsOverview(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$registrations) {
            return null;
        }

        $teamMembers = App::make(Api\GlobalReport::class)->getClassListByCenter($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a = new Arrangements\TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a = new Arrangements\TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData = [];
        $statsReports = [];
        $teamCounts = [
            'team1' => [
                'applications' => [],
                'incoming' => 0,
                'ongoing' => 0,
            ],
            'team2' => [
                'applications' => [],
                'incoming' => 0,
                'ongoing' => 0,
            ],
        ];
        foreach ($teamMembersByCenter as $centerName => $unused) {
            $a = new Arrangements\TeamMemberIncomingOverview([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName] : [],
                'teamMembersData' => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName] : [],
                'region' => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName] = $centerRow['reportData'];
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Models\Center::name($centerName)->first());

            foreach ($centerRow['reportData'] as $team => $teamData) {

                foreach ($teamData['applications'] as $status => $statusCount) {
                    if (!isset($teamCounts[$team]['applications'][$status])) {
                        $teamCounts[$team]['applications'][$status] = 0;
                    }

                    $teamCounts[$team]['applications'][$status] += $statusCount;
                }
                $teamCounts[$team]['incoming'] += isset($teamData['incoming']) ? $teamData['incoming'] : 0;
                $teamCounts[$team]['ongoing'] += isset($teamData['ongoing']) ? $teamData['ongoing'] : 0;
            }
        }
        ksort($reportData);

        return view('globalreports.details.applicationsoverview', compact('reportData', 'teamCounts', 'statsReports'));
    }

    protected function getTmlpRegistrationsByCenter(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$registrations) {
            return null;
        }

        $quarter = Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        $a = new Arrangements\TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $centersData = $a->compose();

        $reportData = [];
        foreach ($centersData['reportData'] as $centerName => $data) {
            $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
                'registrationsData' => $data,
                'quarter' => $quarter,
            ]);
            $data = $a->compose();
            $reportData[$centerName] = $data['reportData'];
        }
        ksort($reportData);
        $reportingDate = $globalReport->reportingDate;

        return view('globalreports.details.applicationsbycenter', compact('reportData', 'reportingDate'));
    }

    protected function getTravelReport(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);

        $teamMembers = App::make(Api\GlobalReport::class)->getClassListByCenter($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a = new Arrangements\TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a = new Arrangements\TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData = [];
        $statsReports = [];
        foreach ($teamMembersByCenter as $centerName => $teamMembersData) {

            $a = new Arrangements\TravelRoomingByTeamYear([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName] : [],
                'teamMembersData' => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName] : [],
                'region' => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName] = $centerRow['reportData'];
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Models\Center::name($centerName)->first());
        }
        ksort($reportData);

        return view('globalreports.details.traveloverview', compact('reportData', 'statsReports'));
    }

    protected function getTdoSummary(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $teamMembers = App::make(Api\GlobalReport::class)->getClassListByCenter($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a = new Arrangements\TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $statsReports = [];
        $reportData = [];
        $totals = [
            'team1' => [
                'total' => 0,
                'attended' => 0,
                'percent' => 0,
            ],
            'team2' => [
                'total' => 0,
                'attended' => 0,
                'percent' => 0,
            ],
            'total' => [
                'total' => 0,
                'attended' => 0,
                'percent' => 0,
            ],
        ];

        foreach ($teamMembersByCenter as $centerName => $centerData) {
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Models\Center::name($centerName)->first());
            foreach ($centerData as $memberData) {
                $team = "team{$memberData->teamYear}";

                if (!isset($reportData[$centerName][$team]['total'])) {
                    $reportData[$centerName][$team]['total'] = 0;
                    $reportData[$centerName][$team]['attended'] = 0;
                    $reportData[$centerName][$team]['percent'] = 0;
                }

                if (!$memberData->isActiveMember()) {
                    continue;
                }

                $reportData[$centerName][$team]['total']++;
                $totals[$team]['total']++;
                $totals['total']['total']++;
                if ($memberData->tdo) {
                    $reportData[$centerName][$team]['attended']++;
                    $totals[$team]['attended']++;
                    $totals['total']['attended']++;
                }
            }
        }
        ksort($reportData);

        foreach ($reportData as $centerName => $data) {
            $total = 0;
            $attended = 0;

            foreach (['team1', 'team2'] as $team) {
                if (!isset($reportData[$centerName][$team])) {
                    $reportData[$centerName][$team]['total'] = 0;
                    $reportData[$centerName][$team]['attended'] = 0;
                    $reportData[$centerName][$team]['percent'] = 0;
                    continue;
                } else if ($reportData[$centerName][$team]['total'] == 0) {
                    continue;
                }

                $reportData[$centerName][$team]['percent'] = round(($reportData[$centerName][$team]['attended'] / $reportData[$centerName][$team]['total']) * 100);
                $total += $reportData[$centerName][$team]['total'];
                $attended += $reportData[$centerName][$team]['attended'];
            }
            $reportData[$centerName]['total']['total'] = $total;
            $reportData[$centerName]['total']['attended'] = $attended;
            $reportData[$centerName]['total']['percent'] = $total ? round(($attended / $total) * 100) : 0;
        }

        foreach ($totals as $team => $data) {
            $totals[$team]['percent'] = 0;
            if ($totals[$team]['total']) {
                $totals[$team]['percent'] = round(($totals[$team]['attended'] / $totals[$team]['total']) * 100);
            }
        }

        return view('globalreports.details.tdosummary', compact('reportData', 'totals', 'statsReports'));
    }

    protected function getTeam2RegisteredAtWeekend(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $registrations = App::make(Api\GlobalReport::class)->getApplicationsListByCenter($globalReport, $region, [
            'returnUnprocessed' => true,
        ]);
        if (!$registrations) {
            return null;
        }

        $registeredAtWeekend = [];
        foreach ($registrations as $registration) {
            $statsReport = $registration->statsReport;
            $weekendStartDate = $statsReport->quarter->getQuarterStartDate($statsReport->center);
            if ($registration->teamYear == 2
                && $registration->regDate->gt($weekendStartDate)
                && $registration->regDate->lte($weekendStartDate->copy()->addDays(2))
            ) {
                $registeredAtWeekend[] = $registration;
            }
        }

        $a = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registeredAtWeekend]);
        $data = $a->compose();

        return view('globalreports.details.applicationsbystatus', [
            'reportData' => $data['reportData'],
            'reportingDate' => $globalReport->reportingDate,
        ]);
    }

    protected function getRegPerParticipant(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $reportData = App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter($globalReport, $region);
        if (!$reportData) {
            return null;
        }

        $lastGlobalReport = Models\GlobalReport::reportingDate($globalReport->reportingDate->copy()->subWeek())
            ->first();
        if (!$lastGlobalReport) {
            return null;
        }

        $lastWeekReportData = App::make(Api\GlobalReport::class)->getWeekScoreboardByCenter($lastGlobalReport, $region);

        $statsReportsAll = $this->getStatsReports($globalReport, $region);

        $statsReports = [];
        foreach ($statsReportsAll as $report) {
            $centerName = $report->center->name;
            $statsReports[$centerName] = $report;
        }

        $games = ['cap', 'cpc', 'lf'];
        foreach ($reportData as $centerName => $centerData) {
            $reportData[$centerName]['statsReport'] = $statsReports[$centerName];
        }
        ksort($reportData);

        foreach ($reportData as $centerName => $centerData) {
            $participantCount = Models\TeamMemberData::byStatsReport($statsReports[$centerName])
                ->active()
                ->count();
            $totalWeekly = 0;
            $totalQuarterly = 0;
            foreach ($games as $game) {
                $change = 0;
                $rppWeekly = 0;
                $rppQuarterly = 0;
                if (isset($centerData['actual'])) {
                    $actual = $centerData['actual'][$game];
                    $totalQuarterly += $actual;
                    $rppQuarterly = $actual / $participantCount;

                    if (isset($lastWeekReportData[$centerName]['actual'])) {
                        $change = $actual - $lastWeekReportData[$centerName]['actual'][$game];
                        $totalWeekly += $change;
                        $rppWeekly = $change / $participantCount;
                    }
                }
                $reportData[$centerName]['change'][$game] = $change;
                $reportData[$centerName]['rpp']['week'][$game] = round($rppWeekly, 1);
                $reportData[$centerName]['rpp']['quarter'][$game] = round($rppQuarterly, 1);
            }
            $reportData[$centerName]['rpp']['week']['total'] = round($totalWeekly / $participantCount, 1);
            $reportData[$centerName]['rpp']['quarter']['total'] = round($totalQuarterly / $participantCount, 1);
        }

        return view('globalreports.details.regperparticipant', compact('reportData', 'games'));
    }

    protected function teamMembersData(Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $this->context->getEncapsulation(Encapsulate\GlobalReportTeamMembersData::class, compact('globalReport', 'region'));
    }

    // Get report CTW
    protected function getTeamMemberStatusCtw(Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $this->teamMembersData($globalReport, $region)->getOne('TeamMemberStatusCtw');

    }

    // Get report Transfers
    protected function getTeamMemberStatusTransfer(Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $this->teamMembersData($globalReport, $region)->getOne('TeamMemberStatusTransfer');
    }
    // Get report Withdrawn
    protected function getTeamMemberStatusWithdrawn(Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $this->teamMembersData($globalReport, $region)->getOne('TeamMemberStatusWithdrawn');
    }
    // Get report Overview
    protected function getTeamMemberStatusPotentialsOverview(Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $this->teamMembersData($globalReport, $region)->getOne('PotentialsOverview');
    }
    // Get report Details
    protected function getTeamMemberStatusPotentials(Models\GlobalReport $globalReport, Models\Region $region)
    {
        return $this->teamMembersData($globalReport, $region)->getOne('Potentials');
    }

    protected function getCenterStatsReports(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $statsReports = $globalReport->statsReports()
                                     ->byRegion($region)
                                     ->get();

        if ($statsReports->isEmpty()) {
            return null;
        }

        $statsReportsList = [];

        $total = 0;
        $ontime = 0;
        $late = 0;
        $resubmitted = 0;

        foreach ($statsReports as $report) {

            $statsReportData = [
                'id' => $report->id,
                'center' => $report->center->name,
                'region' => $region->abbreviation,
                'rating' => $report->getRating(),
                'points' => $report->getPoints(),
                'isValidated' => $report->isValidated(),
                'onTime' => false,
                'officialSubmitTime' => '',
                'officialReport' => $report,
            ];

            $timezone = $report->center->timezone;

            if ($report->isOnTime()) {
                $statsReportData['onTime'] = true;
                $statsReportData['officialSubmitTime'] = $report->submittedAt->setTimezone($timezone)
                                                                ->format('M j @ g:ia T');
                $ontime++;
            } else {
                $otherReports = Models\StatsReport::reportingDate($globalReport->reportingDate)
                    ->byCenter($report->center)
                    ->whereNotNull('submitted_at')
                    ->orderBy('submitted_at', 'asc')
                    ->get();

                if (!$otherReports->isEmpty()) {

                    $officialReport = null;
                    foreach ($otherReports as $submitted) {
                        $officialReport = $submitted;
                        if ($officialReport->isOnTime()) {
                            $statsReportData['onTime'] = true;
                            break;
                        }
                    }

                    if ($officialReport && $statsReportData['onTime'] === true) {
                        $statsReportData['officialSubmitTime'] = $officialReport->submittedAt->setTimezone($timezone)
                                                                                ->format('M j @ g:ia T');
                        $statsReportData['officialReport'] = $officialReport;

                        $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($timezone)
                                                                        ->format('M j @ g:ia T');
                        $statsReportData['revisedReport'] = $report;
                        $resubmitted++;
                    } else {
                        $first = $otherReports->first();
                        $statsReportData['officialSubmitTime'] = $first->submittedAt->setTimezone($timezone)
                                                                       ->format('M j @ g:ia T');
                        $statsReportData['officialReport'] = $first;
                        if ($first->id != $report->id) {
                            $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($timezone)
                                                                            ->format('M j @ g:ia T');
                            $statsReportData['revisedReport'] = $report;
                            $resubmitted++;
                        }
                        $late++;
                    }
                }
            }
            $total++;
            $statsReportsList[] = $statsReportData;
        }
        usort($statsReportsList, function ($a, $b) {
            return strcmp($a['center'], $b['center']);
        });

        $boxes = [
            [
                'stat' => $ontime,
                'description' => 'On Time',
            ],
            [
                'stat' => $late,
                'description' => 'Late',
            ],
            [
                'stat' => $resubmitted,
                'description' => 'Inaccurate',
            ],
            [
                'stat' => $total,
                'description' => 'Total',
            ],
        ];

        return view('globalreports.details.statsreports', compact('statsReportsList', 'boxes'));
    }

    protected function coursesData($globalReport, $region)
    {
        return $this->context->getEncapsulation(Encapsulate\GlobalReportCoursesData::class, compact('globalReport', 'region'));
    }

    protected function getCoursesThisWeek($globalReport, $region)
    {
        return $this->coursesData($globalReport, $region)->getOne('CoursesThisWeek');
    }

    // Get report Next 5 Weeks
    protected function getCoursesNextMonth($globalReport, $region)
    {
        return $this->coursesData($globalReport, $region)->getOne('CoursesNextMonth');
    }

    // Get report Upcoming
    protected function getCoursesUpcoming($globalReport, $region)
    {
        return $this->coursesData($globalReport, $region)->getOne('CoursesUpcoming');
    }

    // Get report Completed
    protected function getCoursesCompleted($globalReport, $region)
    {
        return $this->coursesData($globalReport, $region)->getOne('CoursesCompleted');
    }

    // Get report Guest Games
    protected function getCoursesGuestGames($globalReport, $region)
    {
        return $this->coursesData($globalReport, $region)->getOne('CoursesGuestGames');
    }

    // Get report Summary
    protected function getCoursesSummary($globalReport, $region)
    {
        return $this->coursesData($globalReport, $region)->getOne('CoursesSummary');
    }

    public function getWithdrawReport(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $teamMembers = App::make(Api\GlobalReport::class)->getClassListByCenter($globalReport, $region);

        $reportData = [];
        foreach ($teamMembers as $memberData) {

            if ($memberData->xferOut) {
                continue;
            }

            $center = $memberData->teamMember->center;
            $centerName = $center->name;

            $team = "team{$memberData->teamMember->teamYear}";

            if (!isset($reportData[$centerName])) {
                $reportData[$centerName] = [
                    'classroomLeader' => $center->getClassroomLeader(),
                    'team1' => [
                        'totalCount' => 0,
                        'withdrawCount' => 0,
                        'percent' => 0,
                    ],
                    'team2' => [
                        'totalCount' => 0,
                        'withdrawCount' => 0,
                        'percent' => 0,
                    ],
                ];
            }

            $reportData[$centerName][$team]['totalCount']++;
            if ($memberData->withdrawCodeId !== null && $memberData->withdrawCode->code !== 'WB') {
                $reportData[$centerName][$team]['withdrawCount']++;
            }
        }
        ksort($reportData);

        $almostOutOfCompliance = [];

        foreach ($reportData as $centerName => $data) {
            $outOfCompliance = false;

            foreach (['team1', 'team2'] as $team) {
                if ($data[$team]['totalCount'] == 0) {
                    unset($reportData[$centerName][$team]);
                    continue;
                }

                $percent = ($data[$team]['withdrawCount'] / $data[$team]['totalCount']) * 100;
                $reportData[$centerName][$team]['percent'] = $percent;

                if (($data[$team]['totalCount'] >= 20 && $percent >= 5)
                    || ($data[$team]['totalCount'] < 20 && $data[$team]['withdrawCount'] > 1)
                ) {
                    $outOfCompliance = true;
                } else {
                    $withdrawCount = $data[$team]['withdrawCount'] + 1;
                    $percent = ($withdrawCount / $data[$team]['totalCount']) * 100;

                    if (($data[$team]['totalCount'] >= 20 && $percent >= 5)
                        || ($data[$team]['totalCount'] < 20 && $withdrawCount > 1)
                    ) {
                        $almostOutOfCompliance[$centerName][$team] = $reportData[$centerName][$team];
                        $almostOutOfCompliance[$centerName]['classroomLeader'] = $reportData[$centerName]['classroomLeader'];
                    }
                    unset($reportData[$centerName][$team]);
                }
            }

            if (!$outOfCompliance) {
                unset($reportData[$centerName]);
            }
        }

        return view('globalreports.details.withdrawreport', compact('reportData', 'almostOutOfCompliance'));
    }

    public static function getUrl(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $abbr = strtolower($region->abbreviation);
        $date = $globalReport->reportingDate->toDateString();

        return url("/reports/regions/{$abbr}/{$date}");
    }

    protected function getStatsReports(Models\GlobalReport $report, Models\Region $region)
    {
        return $report->statsReports()
                      ->validated()
                      ->byRegion($region)
                      ->get();
    }
}
