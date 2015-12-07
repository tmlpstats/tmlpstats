<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Http\Requests;
use TmlpStats\GlobalReport;
use TmlpStats\Quarter;
use TmlpStats\Region;
use TmlpStats\ReportToken;
use TmlpStats\StatsReport;

use TmlpStats\Reports\Arrangements\CoursesByCenter;
use TmlpStats\Reports\Arrangements\CoursesWithEffectiveness;
use TmlpStats\Reports\Arrangements\GamesByMilestone;
use TmlpStats\Reports\Arrangements\GamesByWeek;
use TmlpStats\Reports\Arrangements\TeamMemberIncomingOverview;
use TmlpStats\Reports\Arrangements\TeamMembersByCenter;
use TmlpStats\Reports\Arrangements\TeamMembersByStatus;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByCenter;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByIncomingQuarter;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByOverdue;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByStatus;
use TmlpStats\Reports\Arrangements\TravelRoomingByTeamYear;
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

        return view('globalreports.show', compact(
            'globalReport',
            'region',
            'reportToken'
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
            case 'courses':
                $response = $this->getCoursesReport($globalReport, $region);
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
            case 'teammemberstatuspotentials':
                $response = $this->getTeamMemberStatus($globalReport, $region, $report);
                break;
            case 'teammemberstatusall':
                $response = $this->getTeamMemberStatusAll($globalReport, $region);
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

        $a          = new GamesByWeek($globalReportData);
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

        $a          = new GamesByWeek($globalReportData);
        $weeklyData = $a->compose();

        $a    = new GamesByMilestone(['weeks' => $weeklyData['reportData'], 'quarter' => $quarter]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    protected function getTmlpRegistrationsByStatus(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $a    = new TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
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

        $a          = new TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $statusData = $a->compose();

        $a    = new TmlpRegistrationsByOverdue(['registrationsData' => $statusData['reportData']]);
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

        $a                     = new TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a                   = new TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData = [];
        $teamCounts = [
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
            $a         = new TeamMemberIncomingOverview([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName]
                    : [],
                'teamMembersData'   => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName]
                    : [],
                'region'            => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName] = $centerRow['reportData'];

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

        return view('globalreports.details.applicationsoverview', compact('reportData', 'teamCounts'));
    }

    protected function getTmlpRegistrationsByCenter(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $quarter = Quarter::getQuarterByDate($globalReport->reportingDate, $region);

        $a           = new TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $centersData = $a->compose();

        $reportData = [];
        foreach ($centersData['reportData'] as $centerName => $data) {
            $a                       = new TmlpRegistrationsByIncomingQuarter([
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

        $a                     = new TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a                   = new TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData = [];
        foreach ($teamMembersByCenter as $centerName => $teamMembersData) {

            $a         = new TravelRoomingByTeamYear([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName]
                    : [],
                'teamMembersData'   => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName]
                    : [],
                'region'            => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName] = $centerRow['reportData'];
        }
        ksort($reportData);

        return view('globalreports.details.traveloverview', compact('reportData'));
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

        return array_merge($data, ['types' => ['t2Potential'], 'registations' => $potentialsThatRegistered]);
    }

    protected function getTeamMemberStatusAll(GlobalReport $globalReport, Region $region)
    {
        $statusTypes = [
            'teammemberstatuswithdrawn',
            'teammemberstatusctw',
            'teammemberstatustransfer',
            'teammemberstatuspotentials',
        ];

        $data = $this->getTeamMemberStatusData($globalReport, $region);
        if (!$data) {
            return null;
        }

        $responseData = [];
        foreach ($statusTypes as $type) {
            $response = $this->getTeamMemberStatus($globalReport, $region, $type, $data);
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
            case 'teammemberstatuspotentials':
                $viewData = $this->getTeamMemberStatusPotentials($data, $globalReport, $region);
                break;
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

        $a = new TeamMembersByStatus(['teamMembersData' => $teamMembers]);
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

            if ($report->isOnTime()) {
                $statsReportData['onTime']             = true;
                $statsReportData['officialSubmitTime'] = $report->submittedAt->setTimezone($report->center->timezone)
                                                                             ->format('M j @ g:ia T');
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
                        $statsReportData['officialSubmitTime'] = $officialReport->submittedAt->setTimezone($report->center->timezone)
                                                                                             ->format('M j @ g:ia T');
                        $statsReportData['officialReport']     = $officialReport;

                        $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($report->center->timezone)
                                                                                     ->format('M j @ g:ia T');
                        $statsReportData['revisedReport']      = $report;
                    } else {
                        $first                                 = $otherReports->first();
                        $statsReportData['officialSubmitTime'] = $first->submittedAt->setTimezone($report->center->timezone)
                                                                                    ->format('M j @ g:ia T');
                        $statsReportData['officialReport']     = $first;
                        if ($first->id != $report->id) {
                            $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($report->center->timezone)
                                                                                         ->format('M j @ g:ia T');
                            $statsReportData['revisedReport']      = $report;
                        }
                    }
                }
            }
            $statsReportsList[] = $statsReportData;
        }
        usort($statsReportsList, [get_class(), 'sortByCenterName']);

        return view('globalreports.details.statsreports', compact('statsReportsList'));
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
                && $courseData->course->startDate->lt($globalReport->reportingDate->copy()->addMonth())
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
            $response = $this->getCoursesStatus($globalReport, $region, $type, $coursesData);
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
        $type = null;
        $byType = true;
        $flatten = true;
        switch ($status) {
            case 'coursesthisweek':
                $targetData = $this->getCoursesThisWeek($data, $globalReport, $region);
                $type = 'completed';
                break;
            case 'coursesnextmonth':
                $targetData = $this->getCoursesNextMonth($data, $globalReport, $region);
                $type = 'upcoming';
                $flatten = false;
                break;
            case 'coursesupcoming':
                $targetData = $this->getCoursesUpcoming($data, $globalReport, $region);
                $type = 'upcoming';
                $flatten = false;
                break;
            case 'coursescompleted':
                $targetData = $this->getCoursesCompleted($data, $globalReport, $region);
                $type = 'completed';
                break;
            case 'coursesguestgames':
                $targetData = $this->getCoursesGuestGames($data, $globalReport, $region);
                $type = 'guests';
                break;
        }

        return $this->displayCoursesReport($targetData, $globalReport, $type, $byType, $flatten);
    }

    protected function displayCoursesReport($coursesData, GlobalReport $globalReport, $type, $byType = false, $flatten = false)
    {
        $a               = new CoursesByCenter(['coursesData' => $coursesData]);
        $coursesByCenter = $a->compose();
        $coursesByCenter = $coursesByCenter['reportData'];

        $centerReportData = [];
        foreach ($coursesByCenter as $centerName => $coursesData) {
            $a         = new CoursesWithEffectiveness([
                'courses'       => $coursesData,
                'reportingDate' => $globalReport->reportingDate,
            ]);
            $centerRow = $a->compose();

            $centerReportData[$centerName] = $centerRow['reportData'];
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

        return view('globalreports.details.courses', compact('reportData', 'type'));
    }

    protected static function sortByCenterName($a, $b)
    {
        return strcmp($a['center'], $b['center']);
    }
}
