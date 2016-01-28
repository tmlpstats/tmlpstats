<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Center;
use TmlpStats\Http\Requests;
use TmlpStats\GlobalReport;
use TmlpStats\Quarter;
use TmlpStats\Region;
use TmlpStats\ReportToken;
use TmlpStats\StatsReport;
use TmlpStats\Reports\Arrangements;

use Illuminate\Http\Request;

use App;
use Gate;
use Response;

class GlobalReportController extends ReportDispatchAbstractController
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth.token');
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $this->authorize('index', GlobalReport::class);

        $globalReports = GlobalReport::orderBy('reporting_date', 'desc')->get();

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
        $globalReport = GlobalReport::findOrFail($id);

        $this->authorize('read', $globalReport);

        $region = $this->getRegion($request, true);

        $reportToken = Gate::allows('readLink', ReportToken::class)
            ? ReportToken::get($globalReport)
            : null;

        $quarter = Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        return view('globalreports.show', compact(
            'globalReport',
            'region',
            'reportToken',
            'quarter'
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

    protected function getStatsReportsNotOnList(GlobalReport $globalReport)
    {
        $statsReports = StatsReport::reportingDate($globalReport->reportingDate)
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
        return GlobalReport::findOrFail($id);
    }

    public function getCacheKey($model, $report)
    {
        $keyBase = parent::getCacheKey($model, $report);

        $region = $this->getRegion(\Request::instance(), true);

        return $region === null
            ? $keyBase
            : "{$keyBase}:region{$region->id}";
    }

    public function getCacheTags($model, $report)
    {
        $tags = parent::getCacheTags($model, $report);

        return array_merge($tags, ["globalReport{$model->id}"]);
    }

    public function runDispatcher(Request $request, $globalReport, $report)
    {
        $region = $this->getRegion($request, true);

        $response = null;
        switch ($report) {
            case 'ratingsummary':
                $response = $this->getRatingSummary($globalReport, $region);
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
                $response = $this->getCoursesStatus($globalReport, $region, $report);
                break;
            case 'coursesall':
                $response = $this->getCoursesAll($globalReport, $region);
                break;
            case 'teammemberstatuswithdrawn':
            case 'teammemberstatusctw':
            case 'teammemberstatustransfer':
            case 'potentialsdetails':
            case 'potentialsoverview':
                $response = $this->getTeamMemberStatus($globalReport, $region, $report);
                break;
            case 'teammemberstatusall':
                $response = $this->getTeamMemberStatusAll($globalReport, $region);
                break;
            case 'applicationst2fromweekend':
                $response = $this->getTeam2RegisteredAtWeekend($globalReport, $region);
                break;
            case 'tdosummary':
                $response = $this->getTdoSummary($globalReport, $region);
                break;
        }

        return $response;
    }


    protected function getRatingSummary(GlobalReport $globalReport, Region $region)
    {
        $statsReports = $globalReport->statsReports()
                                     ->byRegion($region)
                                     ->validated()
                                     ->get();

        if ($statsReports->isEmpty()) {
            return null;
        }

        $globalReportData = App::make(CenterStatsController::class)
                               ->getByGlobalReport($globalReport, $region, $globalReport->reportingDate);
        if (!$globalReportData) {
            return null;
        }

        $a          = new Arrangements\GamesByWeek($globalReportData);
        $weeklyData = $a->compose();

        $a    = new Arrangements\RegionByRating($statsReports);
        $data = $a->compose();

        $dateString                = $globalReport->reportingDate->toDateString();
        $data['summary']['points'] = $weeklyData['reportData'][$dateString]['points']['total'];
        $data['summary']['rating'] = $weeklyData['reportData'][$dateString]['rating'];

        return view('globalreports.details.ratingsummary', $data);
    }

    protected function getRegionalStats(GlobalReport $globalReport, Region $region)
    {
        $globalReportData = App::make(CenterStatsController::class)->getByGlobalReport($globalReport, $region);
        if (!$globalReportData) {
            return null;
        }

        $quarter = Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        $a          = new Arrangements\GamesByWeek($globalReportData);
        $weeklyData = $a->compose();

        $a    = new Arrangements\GamesByMilestone(['weeks' => $weeklyData['reportData'], 'quarter' => $quarter]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    protected function getGamesByCenter(GlobalReport $globalReport, Region $region)
    {
        $globalReportData = App::make(CenterStatsController::class)
                               ->getByGlobalReportUnprocessed($globalReport, $region, $globalReport->reportingDate);
        if (!$globalReportData) {
            return null;
        }

        $centersData = [];
        foreach ($globalReportData as $centerStatsData) {
            $centerName = $centerStatsData->statsReport->center->name;

            $centersData[$centerName][] = $centerStatsData;
        }

        $reportData = [];
        foreach ($centersData as $centerName => $centerData) {
            $a                       = new Arrangements\GamesByWeek($centerData);
            $dataReport              = $a->compose();
            $reportData[$centerName] = $dataReport['reportData'][$globalReport->reportingDate->toDateString()];
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

    protected function getRepromisesByCenter(GlobalReport $globalReport, Region $region)
    {
        $quarter = Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        $globalReportData = App::make(CenterStatsController::class)
                               ->getByGlobalReportUnprocessed($globalReport, $region, $quarter->getQuarterEndDate(), true);
        if (!$globalReportData) {
            return null;
        }

        $centersData = [];
        foreach ($globalReportData as $centerStatsData) {
            if (!$centerStatsData) {
                continue;
            }

            $centerName = $centerStatsData->statsReport->center->name;

            $centersData[$centerName][] = $centerStatsData;
        }

        $reportData = [];
        foreach ($centersData as $centerName => $centerData) {
            $a                       = new Arrangements\GamesByWeek($centerData);
            $dataReport              = $a->compose();
            $reportData[$centerName] = $dataReport['reportData'][$quarter->getQuarterEndDate()->toDateString()];
        }

        $totals = [];
        foreach ($reportData as $centerName => $centerData) {
            foreach ($centerData['promise'] as $game => $gameData) {
                if (!isset($totals[$game])) {
                    $totals[$game]['original'] = 0;
                    $totals[$game]['promise']  = 0;
                    $totals[$game]['delta']    = 0;
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
        $totals['gitw']['promise']  = round($totals['gitw']['promise'] / count($reportData));
        $totals['gitw']['delta']    = ($totals['gitw']['promise'] - $totals['gitw']['original']);
        if (isset($centerData['actual'])) {
            $totals['gitw']['actual'] = round($totals['gitw']['actual'] / count($reportData));
        }

        $includeActual   = $globalReport->reportingDate->eq($quarter->getQuarterEndDate());
        $includeOriginal = true;

        return view('globalreports.details.centergames', compact(
            'reportData',
            'totals',
            'includeActual',
            'includeOriginal'
        ));
    }

    protected function getTmlpRegistrationsByStatus(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $a    = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $data = $a->compose();

        $data = array_merge($data, ['reportingDate' => $globalReport->reportingDate]);

        return view('globalreports.details.applicationsbystatus', $data);
    }

    protected function getTmlpRegistrationsOverdue(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $a          = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $statusData = $a->compose();

        $a    = new Arrangements\TmlpRegistrationsByOverdue(['registrationsData' => $statusData['reportData']]);
        $data = $a->compose();

        $data = array_merge($data, ['reportingDate' => $globalReport->reportingDate]);

        return view('globalreports.details.applicationsoverdue', $data);
    }

    protected function getTmlpRegistrationsOverview(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $teamMembers = App::make(TeamMembersController::class)->getByGlobalReport($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a                     = new Arrangements\TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a                   = new Arrangements\TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData   = [];
        $statsReports = [];
        $teamCounts   = [
            'team1' => [
                'applications' => [],
                'incoming'     => 0,
                'ongoing'      => 0,
            ],
            'team2' => [
                'applications' => [],
                'incoming'     => 0,
                'ongoing'      => 0,
            ],
        ];
        foreach ($teamMembersByCenter as $centerName => $unused) {
            $a         = new Arrangements\TeamMemberIncomingOverview([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName]
                    : [],
                'teamMembersData'   => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName]
                    : [],
                'region'            => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName]   = $centerRow['reportData'];
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Center::name($centerName)->first());

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

    protected function getTmlpRegistrationsByCenter(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $quarter = Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        $a           = new Arrangements\TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $centersData = $a->compose();

        $reportData = [];
        foreach ($centersData['reportData'] as $centerName => $data) {
            $a                       = new Arrangements\TmlpRegistrationsByIncomingQuarter([
                'registrationsData' => $data,
                'quarter'           => $quarter,
            ]);
            $data                    = $a->compose();
            $reportData[$centerName] = $data['reportData'];
        }
        ksort($reportData);
        $reportingDate = $globalReport->reportingDate;

        return view('globalreports.details.applicationsbycenter', compact('reportData', 'reportingDate'));
    }

    protected function getTravelReport(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $teamMembers = App::make(TeamMembersController::class)->getByGlobalReport($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a                     = new Arrangements\TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a                   = new Arrangements\TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData   = [];
        $statsReports = [];
        foreach ($teamMembersByCenter as $centerName => $teamMembersData) {

            $a         = new Arrangements\TravelRoomingByTeamYear([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName]
                    : [],
                'teamMembersData'   => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName]
                    : [],
                'region'            => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName]   = $centerRow['reportData'];
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Center::name($centerName)->first());
        }
        ksort($reportData);

        return view('globalreports.details.traveloverview', compact('reportData', 'statsReports'));
    }

    protected function getTdoSummary(GlobalReport $globalReport, Region $region)
    {
        $teamMembers = App::make(TeamMembersController::class)->getByGlobalReport($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a                   = new Arrangements\TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $statsReports = [];
        $reportData   = [];
        $totals       = [
            'team1' => [
                'total'    => 0,
                'attended' => 0,
            ],
            'team2' => [
                'total'    => 0,
                'attended' => 0,
            ],
        ];
        foreach ($teamMembersByCenter as $centerName => $centerData) {
            $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Center::name($centerName)->first());
            foreach ($centerData as $memberData) {
                $team = "team{$memberData->teamYear}";

                if (!isset($reportData[$centerName][$team]['total'])) {
                    $reportData[$centerName][$team]['total']    = 0;
                    $reportData[$centerName][$team]['attended'] = 0;
                }

                if (!$memberData->isActiveMember()) {
                    continue;
                }

                $reportData[$centerName][$team]['total']++;
                $totals[$team]['total']++;
                if ($memberData->tdo) {
                    $reportData[$centerName][$team]['attended']++;
                    $totals[$team]['attended']++;
                }
            }
        }
        ksort($reportData);

        return view('globalreports.details.tdosummary', compact('reportData', 'totals', 'statsReports'));
    }

    protected function getTeamMemberStatusWithdrawn($data, GlobalReport $globalReport, Region $region)
    {
        return $data
            ? array_merge($data, ['types' => ['withdrawn']])
            : null;
    }

    protected function getTeamMemberStatusCtw($data, GlobalReport $globalReport, Region $region)
    {
        return $data
            ? array_merge($data, ['types' => ['ctw']])
            : null;
    }

    protected function getTeamMemberStatusTransfer($data, GlobalReport $globalReport, Region $region)
    {
        return $data
            ? array_merge($data, ['types' => ['xferIn', 'xferOut']])
            : null;
    }

    protected function getTeamMemberStatusPotentials($data, GlobalReport $globalReport, Region $region)
    {
        if (!$data) {
            return null;
        } else if (!isset($data['registrations'])) {
            $potenialsData = $this->getTeamMemberStatusPotentialsData($data, $globalReport, $region);
        } else {
            $potenialsData = [];
        }

        return array_merge($data, $potenialsData, ['types' => ['t2Potential']]);
    }

    protected function getTeamMemberStatusPotentialsOverview($data, GlobalReport $globalReport, Region $region)
    {
        if (!$data) {
            return null;
        } else if (!isset($data['registrations'])) {
            $details = $this->getTeamMemberStatusPotentialsData($data, $globalReport, $region);
        } else {
            $details = $data;
        }

        $reportData = [];
        $totals     = [
            'total'      => 0,
            'registered' => 0,
            'approved'   => 0,
        ];

        foreach ($details['reportData']['t2Potential'] as $member) {
            $centerName = $member->center->name;

            if (!isset($reportData[$centerName])) {
                $reportData[$centerName] = [
                    'total'      => 0,
                    'registered' => 0,
                    'approved'   => 0,
                ];
            }
            $reportData[$centerName]['total']++;
            $totals['total']++;

            if (isset($details['registrations'][$member->teamMember->personId])) {
                $reportData[$centerName]['registered']++;
                $totals['registered']++;
                if ($details['registrations'][$member->teamMember->personId]->apprDate) {
                    $reportData[$centerName]['approved']++;
                    $totals['approved']++;
                }
            }

            if (!isset($statsReports[$centerName])) {
                $statsReports[$centerName] = $globalReport->getStatsReportByCenter(Center::name($centerName)->first());
            }
        }

        return view('globalreports.details.potentialsoverview', compact('reportData', 'totals', 'statsReports'));
    }

    protected function getTeamMemberStatusPotentialsData($data, GlobalReport $globalReport, Region $region)
    {
        if (!$data) {
            return null;
        }

        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);

        $potentialsThatRegistered = [];
        if ($registrations) {

            $potentials = $data['reportData']['t2Potential'];
            foreach ($registrations as $registration) {
                if ($registration->teamYear == 2) {
                    foreach ($potentials as $member) {
                        if ($member->teamMember->personId == $registration->registration->persionId) {
                            $potentialsThatRegistered[$member->teamMember->personId] = $registration;
                        }
                    }
                }
            }
        }

        return array_merge($data, ['registations' => $potentialsThatRegistered]);
    }

    protected function getTeam2RegisteredAtWeekend(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);

        $registeredAtWeekend = [];
        if ($registrations) {
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
        }

        $a    = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registeredAtWeekend]);
        $data = $a->compose();

        $data = array_merge($data, ['reportingDate' => $globalReport->reportingDate]);

        return view('globalreports.details.applicationsbystatus', $data);
    }

    protected function getTeamMemberStatusAll(GlobalReport $globalReport, Region $region)
    {
        $statusTypes = [
            'teammemberstatuswithdrawn',
            'teammemberstatusctw',
            'teammemberstatustransfer',
        ];

        $data = $this->getTeamMemberStatusData($globalReport, $region);
        if (!$data) {
            return null;
        }

        $responseData = [];
        foreach ($statusTypes as $type) {
            $response            = $this->getTeamMemberStatus($globalReport, $region, $type, $data);
            $responseData[$type] = $response
                ? $response->render()
                : '';
        }

        $potentialsData = $this->getTeamMemberStatusPotentialsData($data, $globalReport, $region);

        // The potentials reports use the same specialty data, so reuse it instead of processing twice
        $potentialTypes = [
            'potentialsdetails',
            'potentialsoverview',
        ];

        foreach ($potentialTypes as $type) {
            $response            = $this->getTeamMemberStatus($globalReport, $region, $type, $potentialsData);
            $responseData[$type] = $response
                ? $response->render()
                : '';
        }

        return $responseData;
    }

    protected function getTeamMemberStatus(GlobalReport $globalReport, Region $region, $status, $data = null)
    {
        if (!$data) {
            $data = $this->getTeamMemberStatusData($globalReport, $region);
            if (!$data) {
                return null;
            }
        }

        $viewData = null;
        switch ($status) {
            case 'teammemberstatuswithdrawn':
                $viewData = $this->getTeamMemberStatusWithdrawn($data, $globalReport, $region);
                break;
            case 'teammemberstatusctw':
                $viewData = $this->getTeamMemberStatusCtw($data, $globalReport, $region);
                break;
            case 'teammemberstatustransfer':
                $viewData = $this->getTeamMemberStatusTransfer($data, $globalReport, $region);
                break;
            case 'potentialsdetails':
                $viewData = $this->getTeamMemberStatusPotentials($data, $globalReport, $region);
                break;
            case 'potentialsoverview':
                // Potentials Overview uses it's own view
                return $this->getTeamMemberStatusPotentialsOverview($data, $globalReport, $region);
        }

        return $viewData
            ? view('globalreports.details.teammemberstatus', $viewData)
            : null;
    }

    protected function getTeamMemberStatusData(GlobalReport $globalReport, Region $region)
    {
        $teamMembers = App::make(TeamMembersController::class)->getByGlobalReport($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a = new Arrangements\TeamMembersByStatus(['teamMembersData' => $teamMembers]);

        return $a->compose();
    }

    protected function getCenterStatsReports(GlobalReport $globalReport, Region $region)
    {
        $statsReports = $globalReport->statsReports()
                                     ->byRegion($region)
                                     ->get();

        if ($statsReports->isEmpty()) {
            return null;
        }

        $statsReportsList = [];

        $total       = 0;
        $ontime      = 0;
        $late        = 0;
        $resubmitted = 0;

        foreach ($statsReports as $report) {

            $statsReportData = [
                'id'                 => $report->id,
                'center'             => $report->center->name,
                'region'             => $region->abbreviation,
                'rating'             => $report->getRating(),
                'points'             => $report->getPoints(),
                'isValidated'        => $report->isValidated(),
                'onTime'             => false,
                'officialSubmitTime' => '',
                'officialReport'     => $report,
            ];

            $timezone = $report->center->timezone;

            if ($report->isOnTime()) {
                $statsReportData['onTime']             = true;
                $statsReportData['officialSubmitTime'] = $report->submittedAt->setTimezone($timezone)
                                                                             ->format('M j @ g:ia T');
                $ontime++;
            } else {
                $otherReports = StatsReport::reportingDate($globalReport->reportingDate)
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
                        $statsReportData['officialReport']     = $officialReport;

                        $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($timezone)
                                                                                     ->format('M j @ g:ia T');
                        $statsReportData['revisedReport']      = $report;
                        $resubmitted++;
                    } else {
                        $first                                 = $otherReports->first();
                        $statsReportData['officialSubmitTime'] = $first->submittedAt->setTimezone($timezone)
                                                                                    ->format('M j @ g:ia T');
                        $statsReportData['officialReport']     = $first;
                        if ($first->id != $report->id) {
                            $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($timezone)
                                                                                         ->format('M j @ g:ia T');
                            $statsReportData['revisedReport']      = $report;
                            $resubmitted++;
                        }
                        $late++;
                    }
                }
            }
            $total++;
            $statsReportsList[] = $statsReportData;
        }
        usort($statsReportsList, [get_class(), 'sortByCenterName']);

        $boxes = [
            [
                'stat'        => $ontime,
                'description' => 'On Time',
            ],
            [
                'stat'        => $late,
                'description' => 'Late',
            ],
            [
                'stat'        => $resubmitted,
                'description' => 'Inaccurate',
            ],
            [
                'stat'        => $total,
                'description' => 'Total',
            ],
        ];

        return view('globalreports.details.statsreports', compact('statsReportsList', 'boxes'));
    }

    protected function getCoursesThisWeek($coursesData, GlobalReport $globalReport, Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->lt($globalReport->reportingDate)
                && $courseData->course->startDate->gt($globalReport->reportingDate->copy()->subWeek())
            ) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesNextMonth($coursesData, GlobalReport $globalReport, Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->gt($globalReport->reportingDate)
                && $courseData->course->startDate->lt($globalReport->reportingDate->copy()->addWeeks(5))
            ) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesUpcoming($coursesData, GlobalReport $globalReport, Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->gt($globalReport->reportingDate)) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesCompleted($coursesData, GlobalReport $globalReport, Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->lt($globalReport->reportingDate)) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesGuestGames($coursesData, GlobalReport $globalReport, Region $region)
    {
        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->guestsPromised !== null) {
                $targetCourses[] = $courseData;
            }
        }

        return $targetCourses;
    }

    protected function getCoursesAll(GlobalReport $globalReport, Region $region)
    {
        $statusTypes = [
            'coursesthisweek',
            'coursesnextmonth',
            'coursesupcoming',
            'coursescompleted',
            'coursesguestgames',
        ];

        $coursesData = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
        if (!$coursesData) {
            return null;
        }

        $responseData = [];
        foreach ($statusTypes as $type) {
            $response            = $this->getCoursesStatus($globalReport, $region, $type, $coursesData);
            $responseData[$type] = $response
                ? $response->render()
                : '';
        }

        return $responseData;
    }

    protected function getCoursesStatus(GlobalReport $globalReport, Region $region, $status, $data = null)
    {
        if (!$data) {
            $data = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
            if (!$data) {
                return null;
            }
        }

        $targetData = null;
        $type       = null;
        $byType     = true;
        $flatten    = true;
        switch ($status) {
            case 'coursesthisweek':
                $targetData = $this->getCoursesThisWeek($data, $globalReport, $region);
                $type       = 'completed';
                break;
            case 'coursesnextmonth':
                $targetData = $this->getCoursesNextMonth($data, $globalReport, $region);
                $type       = 'upcoming';
                $flatten    = false;
                break;
            case 'coursesupcoming':
                $targetData = $this->getCoursesUpcoming($data, $globalReport, $region);
                $type       = 'upcoming';
                $flatten    = false;
                break;
            case 'coursescompleted':
                $targetData = $this->getCoursesCompleted($data, $globalReport, $region);
                $type       = 'completed';
                break;
            case 'coursesguestgames':
                $targetData = $this->getCoursesGuestGames($data, $globalReport, $region);
                $type       = 'guests';
                break;
        }

        return $this->displayCoursesReport($targetData, $globalReport, $type, $byType, $flatten);
    }

    protected function displayCoursesReport($coursesData, GlobalReport $globalReport, $type, $byType = false, $flatten = false)
    {
        $a               = new Arrangements\CoursesByCenter(['coursesData' => $coursesData]);
        $coursesByCenter = $a->compose();
        $coursesByCenter = $coursesByCenter['reportData'];

        $statsReports     = [];
        $centerReportData = [];
        foreach ($coursesByCenter as $centerName => $coursesData) {
            $a         = new Arrangements\CoursesWithEffectiveness([
                'courses'       => $coursesData,
                'reportingDate' => $globalReport->reportingDate,
            ]);
            $centerRow = $a->compose();

            $centerReportData[$centerName] = $centerRow['reportData'];
            $statsReports[$centerName]     = $globalReport->getStatsReportByCenter(Center::name($centerName)->first());
        }
        ksort($centerReportData);


        if ($byType) {
            $typeReportData = [
                'CAP'       => [],
                'CPC'       => [],
                'completed' => [],
            ];

            foreach ($centerReportData as $centerName => $coursesData) {
                foreach ($coursesData as $courseType => $courseTypeData) {
                    foreach ($courseTypeData as $courseData) {
                        $typeReportData[$courseType][] = $courseData;
                    }
                }
            }

            if ($flatten) {
                $reportData = [];
                foreach (['CAP', 'CPC', 'completed'] as $courseType) {
                    if (isset($typeReportData[$courseType])) {
                        foreach ($typeReportData[$courseType] as $data) {
                            $reportData[] = $data;
                        }
                    }
                }
            } else {
                // Make sure they come out in the right order
                foreach ($typeReportData as $courseType => $data) {
                    if (!$data) {
                        unset($typeReportData[$courseType]);
                    }
                }
                $reportData = $typeReportData;
            }
        } else {
            $reportData = $centerReportData;
        }

        return view('globalreports.details.courses', compact('reportData', 'type', 'statsReports'));
    }

    protected static function sortByCenterName($a, $b)
    {
        return strcmp($a['center'], $b['center']);
    }
}
