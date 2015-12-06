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
     * @return Response
     */
    public function update($id)
    {

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
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

        $centers = array();
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
                $response = $this->getCoursesThisWeek($globalReport, $region);
                break;
            case 'coursesnextmonth':
                $response = $this->getCoursesNextMonth($globalReport, $region);
                break;
            case 'coursesupcoming':
                $response = $this->getCoursesUpcoming($globalReport, $region);
                break;
            case 'coursescompleted':
                $response = $this->getCoursesCompleted($globalReport, $region);
                break;
            case 'coursesguestgames':
                $response = $this->getCoursesGuestGames($globalReport, $region);
                break;
            case 'teammemberstatuswithdrawn':
                $response = $this->getTeamMemberStatusWithdrawn($globalReport, $region);
                break;
            case 'teammemberstatusctw':
                $response = $this->getTeamMemberStatusCtw($globalReport, $region);
                break;
            case 'teammemberstatustransfer':
                $response = $this->getTeamMemberStatusTransfer($globalReport, $region);
                break;
            case 'teammemberstatuspotentials':
                $response = $this->getTeamMemberStatusPotentials($globalReport, $region);
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

        $globalReportData = App::make(CenterStatsController::class)->getByGlobalReport($globalReport, $region, $globalReport->reportingDate);
        if (!$globalReportData) {
            return null;
        }

        $a = new GamesByWeek($globalReportData);
        $weeklyData = $a->compose();

        $a = new Arrangements\RegionByRating($statsReports);
        $data = $a->compose();

        $dateString = $globalReport->reportingDate->toDateString();
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

        $a = new GamesByWeek($globalReportData);
        $weeklyData = $a->compose();

        $a = new GamesByMilestone(['weeks' => $weeklyData['reportData'], 'quarter' => $quarter]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    protected function getTmlpRegistrationsByStatus(GlobalReport $globalReport, Region $region)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
        if (!$registrations) {
            return null;
        }

        $a = new TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
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

        $a = new TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $statusData = $a->compose();

        $a = new TmlpRegistrationsByOverdue(['registrationsData' => $statusData['reportData']]);
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

        $a = new TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a = new TeamMembersByCenter(['teamMembersData' => $teamMembers]);
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
            $a = new TeamMemberIncomingOverview([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName] : [],
                'teamMembersData'   => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName] : [],
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

        $a = new TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $centersData = $a->compose();

        $reportData = [];
        foreach ($centersData['reportData'] as $centerName => $data) {
            $a = new TmlpRegistrationsByIncomingQuarter(['registrationsData' => $data, 'quarter' => $quarter]);
            $data = $a->compose();
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

        $a = new TmlpRegistrationsByCenter(['registrationsData' => $registrations]);
        $registrationsByCenter = $a->compose();
        $registrationsByCenter = $registrationsByCenter['reportData'];

        $a = new TeamMembersByCenter(['teamMembersData' => $teamMembers]);
        $teamMembersByCenter = $a->compose();
        $teamMembersByCenter = $teamMembersByCenter['reportData'];

        $reportData = [];
        foreach ($teamMembersByCenter as $centerName => $teamMembersData) {

            $a = new TravelRoomingByTeamYear([
                'registrationsData' => isset($registrationsByCenter[$centerName]) ? $registrationsByCenter[$centerName] : [],
                'teamMembersData'   => isset($teamMembersByCenter[$centerName]) ? $teamMembersByCenter[$centerName] : [],
                'region'            => $region,
            ]);
            $centerRow = $a->compose();

            $reportData[$centerName] = $centerRow['reportData'];
        }
        ksort($reportData);

        return view('globalreports.details.traveloverview', compact('reportData'));
    }

    protected function getTeamMemberStatusWithdrawn(GlobalReport $globalReport, Region $region)
    {
        return $this->getTeamMemberStatus($globalReport, $region, ['withdrawn']);
    }

    protected function getTeamMemberStatusCtw(GlobalReport $globalReport, Region $region)
    {
        return $this->getTeamMemberStatus($globalReport, $region, ['ctw']);
    }

    protected function getTeamMemberStatusTransfer(GlobalReport $globalReport, Region $region)
    {
        return $this->getTeamMemberStatus($globalReport, $region, ['xferIn', 'xferOut']);
    }

    protected function getTeamMemberStatusPotentials(GlobalReport $globalReport, Region $region)
    {
        return $this->getTeamMemberStatus($globalReport, $region, ['t2Potential']);
    }

    protected function getTeamMemberStatus(GlobalReport $globalReport, Region $region, $types = null)
    {
        if (!$types) {
            $types = ['xferIn', 'xferOut', 'ctw', 'withdrawn', 't2Potential'];
        }

        $teamMembers = App::make(TeamMembersController::class)->getByGlobalReport($globalReport, $region);
        if (!$teamMembers) {
            return null;
        }

        $a = new TeamMembersByStatus(['teamMembersData' => $teamMembers]);
        $data = $a->compose();

        if (in_array('t2Potential', $types)) {
            $registrations = App::make(TmlpRegistrationsController::class)->getByGlobalReport($globalReport, $region);
            if (!$registrations) {
                return null;
            }

            $potentials = $data['reportData']['t2Potential'];
            $potentialsThatRegistered = [];

            foreach ($registrations as $registration) {
                if ($registration->teamYear == 2) {
                    foreach ($potentials as $member) {
                        if ($member->teamMember->personId == $registration->registration->persionId) {
                            $potentialsThatRegistered[$member->teamMember->personId] = $registration;
                        }
                    }
                }
            }
            $data = array_merge($data, ['registations' => $potentialsThatRegistered]);
        }

        $data = array_merge($data, ['types' => $types]);
        return view('globalreports.details.teammemberstatus', $data);
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
                $statsReportData['onTime'] = true;
                $statsReportData['officialSubmitTime'] = $report->submittedAt->setTimezone($report->center->timezone)->format('M j @ g:ia T');
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
                        $statsReportData['officialSubmitTime'] = $officialReport->submittedAt->setTimezone($report->center->timezone)->format('M j @ g:ia T');
                        $statsReportData['officialReport'] = $officialReport;

                        $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($report->center->timezone)->format('M j @ g:ia T');
                        $statsReportData['revisedReport'] = $report;
                    } else {
                        $first = $otherReports->first();
                        $statsReportData['officialSubmitTime'] = $first->submittedAt->setTimezone($report->center->timezone)->format('M j @ g:ia T');
                        $statsReportData['officialReport'] = $first;
                        if ($first->id != $report->id) {
                            $statsReportData['revisionSubmitTime'] = $report->submittedAt->setTimezone($report->center->timezone)->format('M j @ g:ia T');
                            $statsReportData['revisedReport'] = $report;
                        }
                    }
                }
            }
            $statsReportsList[] = $statsReportData;
        }
        usort($statsReportsList, array(get_class(), 'sortByCenterName'));

        return view('globalreports.details.statsreports', compact('statsReportsList'));
    }

    protected function getCoursesThisWeek(GlobalReport $globalReport, Region $region)
    {
        $coursesData = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
        if (!$coursesData) {
            return null;
        }

        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->lt($globalReport->reportingDate)
                && $courseData->course->startDate->gt($globalReport->reportingDate->copy()->subWeek())
            ) {
                $targetCourses[] = $courseData;
            }
        }

        return $this->displayCoursesReport($targetCourses, $globalReport, 'completed', true, true);
    }

    protected function getCoursesNextMonth(GlobalReport $globalReport, Region $region)
    {
        $coursesData = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
        if (!$coursesData) {
            return null;
        }

        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->gt($globalReport->reportingDate)
                && $courseData->course->startDate->lt($globalReport->reportingDate->copy()->addMonth())
            ) {
                $targetCourses[] = $courseData;
            }
        }

        return $this->displayCoursesReport($targetCourses, $globalReport, 'upcoming', true);
    }

    protected function getCoursesUpcoming(GlobalReport $globalReport, Region $region)
    {
        $coursesData = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
        if (!$coursesData) {
            return null;
        }

        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->gt($globalReport->reportingDate)) {
                $targetCourses[] = $courseData;
            }
        }

        return $this->displayCoursesReport($targetCourses, $globalReport, 'upcoming', true);
    }

    protected function getCoursesCompleted(GlobalReport $globalReport, Region $region)
    {
        $coursesData = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
        if (!$coursesData) {
            return null;
        }

        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->course->startDate->lt($globalReport->reportingDate)) {
                $targetCourses[] = $courseData;
            }
        }

        return $this->displayCoursesReport($targetCourses, $globalReport, 'completed', true, true);
    }

    protected function getCoursesGuestGames(GlobalReport $globalReport, Region $region)
    {
        $coursesData = App::make(CoursesController::class)->getByGlobalReport($globalReport, $region);
        if (!$coursesData) {
            return null;
        }

        $targetCourses = [];
        foreach ($coursesData as $courseData) {
            if ($courseData->guestsPromised !== null) {
                $targetCourses[] = $courseData;
            }
        }

        return $this->displayCoursesReport($targetCourses, $globalReport, 'guests', true, true);
    }

    protected function displayCoursesReport($coursesData, GlobalReport $globalReport, $type, $byType = false, $flatten = false)
    {
        $a = new CoursesByCenter(['coursesData' => $coursesData]);
        $coursesByCenter = $a->compose();
        $coursesByCenter = $coursesByCenter['reportData'];

        $centerReportData = [];
        foreach ($coursesByCenter as $centerName => $coursesData) {
            $a = new CoursesWithEffectiveness(['courses' => $coursesData, 'reportingDate' => $globalReport->reportingDate]);
            $centerRow = $a->compose();

            $centerReportData[$centerName] = $centerRow['reportData'];
        }
        ksort($centerReportData);

        if ($byType) {
            $typeReportData = [
                'CAP' => [],
                'CPC' => [],
                'completed' => []
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
