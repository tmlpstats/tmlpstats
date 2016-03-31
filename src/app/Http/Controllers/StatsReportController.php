<?php namespace TmlpStats\Http\Controllers;

use App;
use Auth;
use Cache;
use Carbon\Carbon;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Input;
use Log;
use Response;
use TmlpStats\Api;
use TmlpStats\Center;
use TmlpStats\CourseData;
use TmlpStats\GlobalReport;
use TmlpStats\Import\ImportManager;
use TmlpStats\Import\Xlsx\XlsxArchiver;
use TmlpStats\Import\Xlsx\XlsxImporter;
use TmlpStats\Quarter;
use TmlpStats\Reports\Arrangements;
use TmlpStats\ReportToken;
use TmlpStats\StatsReport;
use TmlpStats\TeamMemberData;
use TmlpStats\TmlpRegistrationData;

class StatsReportController extends ReportDispatchAbstractController
{
    const CACHE_TTL = 7 * 24 * 60;
    protected $context;

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
    public function index(Request $request)
    {
        $this->authorize('index', StatsReport::class);

        $selectedRegion = $this->getRegion($request);

        $allReports = StatsReport::currentQuarter($selectedRegion)
            ->groupBy('reporting_date')
            ->orderBy('reporting_date', 'desc')
            ->get();
        if ($allReports->isEmpty()) {
            $allReports = StatsReport::lastQuarter($selectedRegion)
                ->groupBy('reporting_date')
                ->orderBy('reporting_date', 'desc')
                ->get();
        }

        $today = Carbon::now();
        $reportingDates = [];

        if ($today->dayOfWeek == Carbon::FRIDAY) {
            $reportingDates[$today->toDateString()] = $today->format('F j, Y');
        }
        foreach ($allReports as $report) {
            $dateString = $report->reportingDate->toDateString();
            $reportingDates[$dateString] = $report->reportingDate->format('F j, Y');
        }

        $reportingDate = null;
        $reportingDateString = Input::has('stats_report')
        ? Input::get('stats_report')
        : '';

        if ($reportingDateString && isset($reportingDates[$reportingDateString])) {
            $reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDateString);
        } else if ($today->dayOfWeek == Carbon::FRIDAY) {
            $reportingDate = $today;
        } else if (!$reportingDate && $reportingDates) {
            $reportingDate = $allReports[0]->reportingDate;
        } else {
            $reportingDate = ImportManager::getExpectedReportDate();
        }

        $centers = Center::active()
            ->byRegion($selectedRegion)
            ->orderBy('name', 'asc')
            ->get();

        $statsReportList = [];
        foreach ($centers as $center) {
            $report = StatsReport::reportingDate($reportingDate)
                ->byCenter($center)
                ->orderBy('submitted_at', 'desc')
                ->first();
            $statsReportList[$center->name] = [
                'center' => $center,
                'report' => $report,
                'viewable' => $this->authorize('read', $report),
            ];
        }

        return view('statsreports.index', compact(
            'statsReportList',
            'reportingDates',
            'reportingDate',
            'selectedRegion'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * Not used
     *
     * @return Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * Not used
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
        $statsReport = StatsReport::findOrFail($id);
        $this->context->setCenter($statsReport->center);
        $this->context->setReportingDate($statsReport->reportingDate);

        $this->authorize('read', $statsReport);

        $sheetUrl = '';
        $globalReport = null;
        $center = $statsReport->center;

        $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

        if ($sheetPath) {
            $sheetUrl = $sheetPath
            ? url("/statsreports/{$statsReport->id}/download")
            : null;
        }

        $quarterStartDate = $statsReport->quarter->getQuarterStartDate($center);
        $quarterEndDate = $statsReport->quarter->getQuarterEndDate($center);

        // Other Stats Reports
        $otherStatsReports = [];
        $searchWeek = $quarterEndDate->copy();

        while ($searchWeek->gte($quarterStartDate)) {
            $globalReport = GlobalReport::reportingDate($searchWeek)->first();
            if ($globalReport) {
                $report = $globalReport->statsReports()->byCenter($center)->first();
                if ($report) {
                    $otherStatsReports[$report->id] = $report->reportingDate->format('M d, Y');
                }
            }
            $searchWeek->subWeek();
        }

        // Only show last quarter's completion report on the first week
        if ($statsReport->reportingDate->diffInWeeks($quarterStartDate) > 1) {
            array_pop($otherStatsReports);
        }

        // When showing last quarter's data, make sure we also show this week's report
        if ($quarterEndDate->lt(Carbon::now())) {
            $firstWeek = $quarterEndDate->copy();
            $firstWeek->addWeek();

            $globalReport = GlobalReport::reportingDate($firstWeek)->first();
            if ($globalReport) {
                $report = $globalReport->statsReports()->byCenter($center)->first();
                if ($report) {
                    $otherStatsReports = [$report->id => $report->reportingDate->format('M d, Y')] + $otherStatsReports;
                }
            }
        }

        $globalReport = GlobalReport::reportingDate($statsReport->reportingDate)->first();

        $reportToken = null;
        if (Gate::allows('readLink', ReportToken::class)) {
            $reportToken = ReportToken::get($globalReport, $center);
        }

        $showNavCenterSelect = true;

        return view('statsreports.show', compact(
            'statsReport',
            'globalReport',
            'otherStatsReports',
            'sheetUrl',
            'reportToken',
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
        return "Coming soon...";
    }

    /**
     * Update the specified resource in storage.
     *
     * Currently only supports updated locked property
     *
     * @param  int $id
     *
     * @return Response
     */
    public function update($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * Currently only supports updated locked property
     *
     * @param  int $id
     *
     * @return Response
     */
    public function submit($id)
    {
        $statsReport = StatsReport::findOrFail($id);

        $this->authorize($statsReport);

        $userEmail = Auth::user()->email;
        $response = [
            'statsReport' => $id,
            'success' => false,
            'message' => '',
        ];

        $action = Input::get('function', null);
        if ($action === 'submit') {

            $sheetUrl = XlsxArchiver::getInstance()->getSheetPath($statsReport);
            $sheet = [];

            try {
                // Check if we have cached the report already. If so, remove it from the cache and use it here
                $cacheKey = "statsReport{$id}:importdata";
                $importer = Cache::pull($cacheKey);
                if (!$importer) {
                    $importer = new XlsxImporter($sheetUrl, basename($sheetUrl), $statsReport->reportingDate, false);
                    $importer->import();
                }
                $importer->saveReport();
                $sheet = $importer->getResults();
            } catch (Exception $e) {
                Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }

            $statsReport->submittedAt = Carbon::now();
            $statsReport->submitComment = Input::get('comment', null);
            $statsReport->locked = true;

            if ($statsReport->save()) {
                // Cache the validation results so we don't have to regenerate
                $cacheKey = "statsReport{$id}:results";
                Cache::tags(["statsReport{$id}"])->put($cacheKey, $sheet, static::CACHE_TTL);

                XlsxArchiver::getInstance()->promoteWorkingSheet($statsReport);

                $submittedAt = clone $statsReport->submittedAt;
                $submittedAt->setTimezone($statsReport->center->timezone);

                $response['submittedAt'] = $submittedAt->format('g:i A');
                $result = ImportManager::sendStatsSubmittedEmail($statsReport, $sheet);

                if (isset($result['success'])) {
                    $response['success'] = true;
                    $response['message'] = $result['success'][0];
                } else {
                    $response['success'] = false;
                    $response['message'] = $result['error'][0];
                }

                Log::info("User {$userEmail} submitted statsReport {$id}");
            } else {
                $response['message'] = 'Unable to submit stats report.';
                Log::error("User {$userEmail} attempted to submit statsReport {$id}. Failed to submit.");
            }
        } else {
            $response['message'] = 'Invalid request.';
            Log::error("User {$userEmail} attempted to submit statsReport {$id}. No value provided for submitReport. ");
        }

        return $response;
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
    }

    public function downloadSheet($id)
    {
        $statsReport = StatsReport::findOrFail($id);

        $this->authorize($statsReport);

        $path = XlsxArchiver::getInstance()->getSheetPath($statsReport);

        if ($path) {
            $filename = XlsxArchiver::getInstance()->getDisplayFileName($statsReport);

            return Response::download($path, $filename, [
                'Content-Length: ' . filesize($path),
            ]);
        } else {
            abort(404);
        }
    }

    public function getById($id)
    {
        return StatsReport::findOrFail($id);
    }

    public function getCacheTags($model, $report)
    {
        $tags = parent::getCacheTags($model, $report);

        return array_merge($tags, ["statsReport{$model->id}"]);
    }

    public function authorizeReport($statsReport, $report)
    {
        switch ($report) {
            case 'contactinfo':
                $this->authorize('readContactInfo', $statsReport);
            default:
                parent::authorizeReport($statsReport, $report);
        }
    }

    public function runDispatcher(Request $request, $statsReport, $report, $extra = null)
    {
        $this->setCenter($statsReport->center);
        $this->context->setCenter($statsReport->center);
        $this->setReportingDate($statsReport->reportingDate);
        $this->context->setReportingDate($statsReport->reportingDate);

        if (!$statsReport->isValidated() && $report != 'results') {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $response = null;
        switch ($report) {
            case 'summary':
                $response = $this->getSummary($statsReport);
                break;
            case 'results':
                $response = $this->getResults($statsReport);
                break;
            case 'centerstats':
                $response = $this->getCenterStats($statsReport);
                break;
            case 'classlist':
                $response = $this->getClassList($statsReport);
                break;
            case 'tmlpregistrations':
                $response = $this->getTmlpRegistrations($statsReport);
                break;
            case 'tmlpregistrationsbystatus':
                $response = $this->getTmlpRegistrationsByStatus($statsReport);
                break;
            case 'courses':
                $response = $this->getCourses($statsReport);
                break;
            case 'contactinfo':
                $response = $this->getContactInfo($statsReport);
                break;
            case 'gitwsummary':
                $response = $this->getGitwSummary($statsReport);
                break;
            case 'tdosummary':
                $response = $this->getTdoSummary($statsReport);
                break;
            case 'peopletransfersummary':
                $response = $this->getPeopleTransferSummary($statsReport);
                break;
            case 'coursestransfersummary':
                $response = $this->getCoursesTransferSummary($statsReport);
                break;
            case 'teamsummary':
                $response = $this->getTeamWeekendSummary($statsReport);
                break;
            case 'travel':
                $response = $this->getTeamTravelSummary($statsReport);
                break;
            case 'mobile_summary':
                $response = $this->getMobileSummary($statsReport);
        }

        return $response;
    }

    public function getCoursesSummary(StatsReport $statsReport)
    {
        $courses = App::make(CoursesController::class)->getByStatsReport($statsReport);

        $completedCourses = [];
        $upcomingCourses = [];
        if ($courses) {
            // Completed Courses
            $a = new Arrangements\CoursesWithEffectiveness([
                'courses' => $courses,
                'reportingDate' => $statsReport->reportingDate,
            ]);
            $courses = $a->compose();

            $lastWeek = $statsReport->reportingDate->copy()->subWeek();

            if (isset($courses['reportData']['completed'])) {
                foreach ($courses['reportData']['completed'] as $course) {
                    if ($course['startDate']->gte($lastWeek)) {
                        $completedCourses[] = $course;
                    }
                }
            }

            $courseList = [];
            if (isset($courses['reportData']['CAP'])) {
                $courseList = array_merge($courseList, $courses['reportData']['CAP']);
            }

            if (isset($courses['reportData']['CPC'])) {
                $courseList = array_merge($courseList, $courses['reportData']['CPC']);
            }

            foreach ($courseList as $course) {
                if ($course['startDate']->gt($statsReport->reportingDate)
                    && $course['startDate']->lt($statsReport->reportingDate->copy()->addWeeks(3))
                ) {
                    $upcomingCourses[] = $course;
                }
            }
        }

        return compact(
            'completedCourses',
            'upcomingCourses'
        );
    }

    public function getTeamMembersSummary(StatsReport $statsReport)
    {
        $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($statsReport);

        $tdo = [];
        $gitw = [];
        $teamWithdraws = [];
        if ($teamMembers) {
            // Team Member stats
            $a = new Arrangements\TeamMembersCounts(['teamMembersData' => $teamMembers]);
            $teamMembersCounts = $a->compose();

            $tdo = $teamMembersCounts['reportData']['tdo'];
            $gitw = $teamMembersCounts['reportData']['gitw'];
            $teamWithdraws = $teamMembersCounts['reportData']['withdraws'];
        }

        return compact(
            'teamMembers',
            'tdo',
            'gitw',
            'teamWithdraws'
        );
    }

    public function getRegistrationsSummary(StatsReport $statsReport)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport);

        $applications = [];
        $applicationWithdraws = [];
        if ($registrations) {
            // Application Status
            $a = new Arrangements\TmlpRegistrationsByStatus([
                'registrationsData' => $registrations,
                'quarter' => $statsReport->quarter,
            ]);
            $applications = $a->compose();
            $applications = $applications['reportData'];

            // Application Withdraws
            $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
                'registrationsData' => $registrations,
                'quarter' => $statsReport->quarter,
            ]);
            $applicationWithdraws = $a->compose();
            $applicationWithdraws = $applicationWithdraws['reportData']['withdrawn'];
        }

        return compact(
            'registrations',
            'applications',
            'applicationWithdraws'
        );
    }

    public function getTravelSummary(StatsReport $statsReport, $teamMembersSummary, $registrationsSummary)
    {
        $teamTravelDetails = [];
        $incomingTravelDetails = [];
        if ($teamMembersSummary['teamMembers'] && $registrationsSummary['registrations']) {
            // Travel/Room
            $a = new Arrangements\TravelRoomingByTeamYear([
                'registrationsData' => $registrationsSummary['registrations'],
                'teamMembersData' => $teamMembersSummary['teamMembers'],
                'region' => $statsReport->center->region,
            ]);
            $travelDetails = $a->compose();
            $travelDetails = $travelDetails['reportData'];
            $teamTravelDetails = $travelDetails['teamMembers'];
            $incomingTravelDetails = $travelDetails['incoming'];
        }

        return compact(
            'teamTravelDetails',
            'incomingTravelDetails'
        );
    }

    public function getSummaryPageData(StatsReport $statsReport, $live = false)
    {
        $date = $statsReport->reportingDate->toDateString();
        $liveData = null;
        if ($live) {
            $liveData = App::make(Api\LiveScoreboard::class)->getCurrentScores($statsReport->center);
        }

        $centerStatsData = App::make(CenterStatsController::class)
            ->getByStatsReport($statsReport, $statsReport->reportingDate);

        $reportData = [];
        if ($centerStatsData) {
            // Center Games
            $a = new Arrangements\GamesByWeek($centerStatsData);
            $centerStatsData = $a->compose();

            $reportData = $centerStatsData['reportData'][$date];
        }

        $coursesSummary = $this->getCoursesSummary($statsReport);
        $teamMembersSummary = $this->getTeamMembersSummary($statsReport);
        $registrationsSummary = $this->getRegistrationsSummary($statsReport);
        $travelSummary = $this->getTravelSummary($statsReport, $teamMembersSummary, $registrationsSummary);

        $data = compact(
            'liveData',
            'statsReport',
            'reportData',
            'date'
        );

        return array_merge($data, $coursesSummary, $teamMembersSummary, $registrationsSummary, $travelSummary);
    }

    protected function getSummary(StatsReport $statsReport)
    {
        $data = $this->getSummaryPageData($statsReport);
        return view('statsreports.details.summary', $data);
    }

    protected function getMobileSummary(StatsReport $statsReport)
    {
        $data = $this->getSummaryPageData($statsReport, true);
        $data['skip_navbar'] = true;
        $data['liveScoreboard'] = true;
        return view('statsreports.details.mobile_summary', $data);
    }

    protected function getCenterStats(StatsReport $statsReport)
    {
        $a = new Arrangements\GamesByMilestone([
            'weeks' => App::make(Api\LocalReport::class)->getWeeklyPromises($statsReport),
            'quarter' => $statsReport->quarter,
        ]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    protected function getClassList(StatsReport $statsReport)
    {
        return view('statsreports.details.classlist', [
            'reportData' => App::make(Api\LocalReport::class)->getClassListByQuarter($statsReport),
        ]);
    }

    protected function getTmlpRegistrationsByStatus(StatsReport $statsReport)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport);
        if (!$registrations) {
            return null;
        }

        $a = new Arrangements\TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $data = $a->compose();

        $data = array_merge($data, ['reportingDate' => $statsReport->reportingDate]);

        return view('statsreports.details.tmlpregistrationsbystatus', $data);
    }

    protected function getTmlpRegistrations(StatsReport $statsReport)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport);
        if (!$registrations) {
            return null;
        }

        $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
            'registrationsData' => $registrations,
            'quarter' => $statsReport->quarter,
        ]);
        $data = $a->compose();

        return view('statsreports.details.tmlpregistrations', $data);
    }

    protected function getCourses(StatsReport $statsReport)
    {
        $courses = App::make(CoursesController::class)->getByStatsReport($statsReport);
        if (!$courses) {
            return null;
        }

        $a = new Arrangements\CoursesWithEffectiveness(['courses' => $courses, 'reportingDate' => $statsReport->reportingDate]);
        $data = $a->compose();

        return view('statsreports.details.courses', $data);
    }

    protected function getContactInfo(StatsReport $statsReport)
    {
        $contacts = App::make(ContactsController::class)->getByStatsReport($statsReport);
        if (!$contacts) {
            return null;
        }

        return view('statsreports.details.contactinfo', compact('contacts'));
    }

    protected function getGitwSummary(StatsReport $statsReport)
    {
        $weeksData = [];

        $date = $statsReport->quarter->getFirstWeekDate($statsReport->center);
        while ($date->lte($statsReport->reportingDate)) {
            $globalReport = GlobalReport::reportingDate($date)->first();
            $report = $globalReport->statsReports()->byCenter($statsReport->center)->first();
            $weeksData[$date->toDateString()] = $report ? App::make(TeamMembersController::class)
                ->getByStatsReport($report) : null;
            $date->addWeek();
        }
        if (!$weeksData) {
            return null;
        }

        $a = new Arrangements\GitwByTeamMember(['teamMembersData' => $weeksData]);
        $data = $a->compose();

        return view('statsreports.details.teammembersweekly', $data);
    }

    protected function getTdoSummary(StatsReport $statsReport)
    {
        $weeksData = [];

        $date = $statsReport->quarter->getFirstWeekDate($statsReport->center);
        while ($date->lte($statsReport->reportingDate)) {
            $globalReport = GlobalReport::reportingDate($date)->first();
            $report = $globalReport->statsReports()->byCenter($statsReport->center)->first();
            $weeksData[$date->toDateString()] = $report ? App::make(TeamMembersController::class)
                ->getByStatsReport($report) : null;
            $date->addWeek();
        }
        if (!$weeksData) {
            return null;
        }

        $a = new Arrangements\TdoByTeamMember(['teamMembersData' => $weeksData]);
        $data = $a->compose();

        return view('statsreports.details.teammembersweekly', $data);
    }

    protected function getResults(StatsReport $statsReport)
    {
        $sheet = [];
        $sheetUrl = '';

        $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

        if ($sheetPath) {
            try {
                $importer = new XlsxImporter($sheetPath, basename($sheetPath), $statsReport->reportingDate, false);
                $importer->import(false);
                $sheet = $importer->getResults();
            } catch (Exception $e) {
                Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }

            $sheetUrl = $sheetPath
            ? url("/statsreports/{$statsReport->id}/download")
            : null;
        }

        if (!$sheetUrl) {
            return '<p>Results not available.</p>';
        }

        $includeUl = true;

        return view('import.results', compact(
            'statsReport',
            'sheetUrl',
            'sheet',
            'includeUl'
        ));
    }

    protected function getTeamWeekendSummary(StatsReport $statsReport)
    {
        $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($statsReport);
        if (!$teamMembers) {
            return null;
        }

        $registrationsData = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport);

        $registrations = [];
        if ($registrationsData) {
            $nextQuarter = $statsReport->quarter->getNextQuarter();
            foreach ($registrationsData as $registration) {
                if ($registration->incomingQuarterId == $nextQuarter->id) {
                    $registrations[] = $registration;
                }
            }
        }

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMembers]);
        $teamMembers = $a->compose();
        $teamMembers = $teamMembers['reportData'];

        $a = new Arrangements\TmlpRegistrationsByStatus([
            'registrationsData' => $registrations,
            'quarter' => $statsReport->quarter,
        ]);
        $applications = $a->compose();
        $applications = $applications['reportData'];

        $t1Continuing = 0;
        if (isset($teamMembers['team1'])) {
            $t1Continuing = array_sum(array_map('count', $teamMembers['team1']));
            if (isset($teamMembers['team1']['Q4'])) {
                $t1Continuing -= count($teamMembers['team1']['Q4']);
            }
        }
        if (isset($applications['approved'])) {
            foreach ($applications['approved'] as $app) {
                if ($app->teamYear == 1) {
                    $t1Continuing++;
                }
            }
        }

        $t2Continuing = 0;
        if (isset($teamMembers['team2'])) {
            $t2Continuing = array_sum(array_map('count', $teamMembers['team2']));
            if (isset($teamMembers['team2']['Q4'])) {
                $t2Continuing -= count($teamMembers['team2']['Q4']);
            }
        }
        if (isset($applications['approved'])) {
            foreach ($applications['approved'] as $app) {
                if ($app->teamYear == 2) {
                    $t2Continuing++;
                }
            }
        }

        $completingCount = 0;
        if (isset($teamMembers['team1']['Q4'])) {
            $completingCount += count($teamMembers['team1']['Q4']);
        }
        if (isset($teamMembers['team2']['Q4'])) {
            $completingCount += count($teamMembers['team2']['Q4']);
        }

        $incomingCount = isset($applications['approved'])
        ? count($applications['approved'])
        : 0;

        $boxes = [
            [
                'stat' => $t1Continuing,
                'description' => 'Team 1',
            ],
            [
                'stat' => $t2Continuing,
                'description' => 'Team 2',
            ],
            [
                'stat' => $completingCount,
                'description' => 'Completing',
            ],
            [
                'stat' => $incomingCount,
                'description' => 'Incoming',
            ],
        ];

        return view('statsreports.details.teamsummary', compact('teamMembers', 'applications', 'boxes'));
    }

    protected function getTeamTravelSummary(StatsReport $statsReport)
    {
        $teamMembersData = App::make(TeamMembersController::class)->getByStatsReport($statsReport);
        if (!$teamMembersData) {
            return null;
        }

        $registrationsData = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport);

        $registrations = [];
        if ($registrationsData) {
            $nextQuarter = $statsReport->quarter->getNextQuarter();
            foreach ($registrationsData as $registration) {
                if ($registration->incomingQuarterId == $nextQuarter->id) {
                    $registrations[] = $registration;
                }
            }
        }

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMembersData]);
        $teamMembers = $a->compose();
        $teamMembers = $teamMembers['reportData'];

        $a = new Arrangements\TmlpRegistrationsByStatus([
            'registrationsData' => $registrations,
            'quarter' => $statsReport->quarter,
        ]);
        $applications = $a->compose();
        $applications = $applications['reportData'];

        $a = new Arrangements\TravelRoomingByTeamYear([
            'registrationsData' => $registrations,
            'teamMembersData' => $teamMembersData,
            'region' => $statsReport->center->region,
        ]);
        $travelDetails = $a->compose();
        $travelDetails = $travelDetails['reportData'];
        $teamTravelDetails = $travelDetails['teamMembers'];
        $incomingTravelDetails = $travelDetails['incoming'];

        $boxes = [
            [
                'stat' => $teamTravelDetails['team1']['travel'] + $teamTravelDetails['team2']['travel'],
                'subStat' => $teamTravelDetails['team1']['total'] + $teamTravelDetails['team2']['total'],
                'description' => 'Team Travel',
            ],
            [
                'stat' => $teamTravelDetails['team1']['room'] + $teamTravelDetails['team2']['room'],
                'subStat' => $teamTravelDetails['team1']['total'] + $teamTravelDetails['team2']['total'],
                'description' => 'Team Rooming',
            ],
            [
                'stat' => $incomingTravelDetails['team1']['room'] + $incomingTravelDetails['team2']['room'],
                'subStat' => $incomingTravelDetails['team1']['total'] + $incomingTravelDetails['team2']['total'],
                'description' => 'Incoming Travel',
            ],
            [
                'stat' => $incomingTravelDetails['team1']['travel'] + $incomingTravelDetails['team2']['travel'],
                'subStat' => $incomingTravelDetails['team1']['total'] + $incomingTravelDetails['team2']['total'],
                'description' => 'Incoming Rooming',
            ],
        ];

        return view('statsreports.details.travelsummary', compact('teamMembers', 'applications', 'boxes'));
    }

    protected function getPeopleTransferSummary(StatsReport $statsReport)
    {
        $thisWeek = clone $statsReport->reportingDate;

        $globalReport = GlobalReport::reportingDate($thisWeek->subWeek())->first();
        if (!$globalReport) {
            return null;
        }

        $lastStatsReport = $globalReport->getStatsReportByCenter($statsReport->center);
        if (!$lastStatsReport) {
            return null;
        }

        // Get this week and last weeks data organized by team year and quarter
        $teamMemberDataThisWeek = App::make(TeamMembersController::class)->getByStatsReport($statsReport);
        $teamMemberDataLastWeek = App::make(TeamMembersController::class)->getByStatsReport($lastStatsReport);

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMemberDataThisWeek]);
        $teamThisWeekByQuarter = $a->compose();
        $teamThisWeekByQuarter = $teamThisWeekByQuarter['reportData'];

        $a = new Arrangements\TeamMembersByQuarter(['teamMembersData' => $teamMemberDataLastWeek]);
        $teamLastWeekByQuarter = $a->compose();
        $teamLastWeekByQuarter = $teamLastWeekByQuarter['reportData'];

        $incomingDataThisWeek = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport);
        $incomingDataLastWeek = App::make(TmlpRegistrationsController::class)->getByStatsReport($lastStatsReport);

        $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
            'registrationsData' => $incomingDataThisWeek,
            'quarter' => $statsReport->quarter,
        ]);
        $incomingThisWeekByQuarter = $a->compose();
        $incomingThisWeekByQuarter = $incomingThisWeekByQuarter['reportData'];

        $a = new Arrangements\TmlpRegistrationsByIncomingQuarter([
            'registrationsData' => $incomingDataLastWeek,
            'quarter' => $statsReport->quarter,
        ]);
        $incomingLastWeekByQuarter = $a->compose();
        $incomingLastWeekByQuarter = $incomingLastWeekByQuarter['reportData'];

        // Cleanup incoming withdraws
        // Remove people that were withdrawn and moved to the future weekend section
        foreach ($incomingLastWeekByQuarter['withdrawn'] as $quarterNumber => $quarterData) {
            foreach ($quarterData as $idx => $withdrawData) {
                $team = "team{$withdrawData->registration->teamYear}";

                list($field, $idx, $object) = $this->hasPerson([
                    'next',
                    'future',
                ], $withdrawData, $incomingLastWeekByQuarter[$team]);
                if ($field !== null) {
                    unset($incomingLastWeekByQuarter['withdrawn'][$field][$idx]);
                }
            }
        }

        // Keep track of persons of interest
        $incomingSummary = [
            'missing' => [], // Was on last week's sheet but not this week
            'changed' => [], // On both sheets
            'new' => [], // New on this week (could be a WD that's now reregistered)
        ];

        // Find all missing or modified applications
        foreach (['team1', 'team2'] as $team) {
            foreach ($incomingLastWeekByQuarter[$team] as $quarterNumber => $quarterData) {
                foreach ($quarterData as $lastWeekData) {
                    list($field, $idx, $thisWeekData) = $this->hasPerson([
                        'next',
                        'future',
                    ], $lastWeekData, $incomingThisWeekByQuarter[$team]);
                    if ($field !== null) {
                        // We only need to display existing rows that weren't copied properly
                        if (!$this->incomingCopiedCorrectly($thisWeekData, $lastWeekData)) {
                            $incomingSummary['changed'][] = [$thisWeekData, $lastWeekData];
                        }
                        unset($incomingThisWeekByQuarter[$team][$field][$idx]);
                    } else {
                        $incomingSummary['missing'][] = [null, $lastWeekData];
                    }
                }
            }
        }

        // Everything that's left is new.
        // Attach info from any application that was withdrawn last quarter
        foreach (['team1', 'team2'] as $team) {
            foreach ($incomingThisWeekByQuarter[$team] as $quarterNumber => $quarterData) {
                foreach ($quarterData as $thisWeekData) {
                    list($field, $idx, $withdrawData) = $this->hasPerson([
                        'next',
                        'future',
                    ], $thisWeekData, $incomingLastWeekByQuarter['withdrawn']);
                    $incomingSummary['new'][] = [$thisWeekData, $withdrawData];
                }
            }
        }

        $teamMemberSummary = [
            'new' => [],
            'missing' => [],
        ];

        // Find any missing team members (except quarter 4 who are outgoing)
        foreach (['team1', 'team2'] as $team) {
            foreach ($teamLastWeekByQuarter[$team] as $quarterNumber => $quarterData) {
                // Skip Q1 because quarter number calculations wrap, so last quarter's Q4 now looks like a Q1
                if ($quarterNumber == 'Q1') {
                    continue;
                }

                foreach ($quarterData as $lasWeekData) {
                    list($field, $idx, $data) = $this->hasPerson([
                        'Q2',
                        'Q3',
                        'Q4',
                    ], $lasWeekData, $teamThisWeekByQuarter[$team]);
                    if ($field !== null) {
                        // We found it! remove it from the search list
                        unset($teamThisWeekByQuarter[$team][$field][$idx]);
                    } else {
                        $teamMemberSummary['missing'][] = [null, $lasWeekData];
                    }
                }
            }
        }

        // Process new team members
        // By now, we've removed all of the members that matched, so it should all be new team members.
        // Check the incoming and add match any withdrawn members that have reappeared
        foreach (['team1', 'team2'] as $team) {
            foreach ($teamThisWeekByQuarter[$team] as $quarterNumber => $quarterData) {
                foreach ($quarterData as $thisWeekData) {
                    $matched = false;
                    $lastWeekData = null;
                    if ($quarterNumber == 'Q1') {
                        // Match up incoming with new Q1 team
                        foreach ($incomingSummary['missing'] as $idx => $incomingData) {
                            if ($this->objectsAreEqual($incomingData[1], $thisWeekData)) {
                                unset($incomingSummary['missing'][$idx]);
                                $matched = true;
                                break;
                            }
                        }
                    } else if (isset($teamLastWeekByQuarter['withdrawn'][$quarterNumber])) {

                        // Check if the new team member was withdrawn previously
                        list($field, $idx, $withdrawData) = $this->hasPerson([
                            'Q1',
                            'Q2',
                            'Q3',
                            'Q4',
                        ], $thisWeekData, $teamLastWeekByQuarter['withdrawn']);
                        if ($withdrawData !== null) {
                            $lastWeekData = $withdrawData;
                        }
                    }
                    if (!$matched) {
                        $teamMemberSummary['new'][] = [$thisWeekData, $lastWeekData];
                    }
                }
            }
        }

        $thisQuarter = Quarter::getCurrentQuarter($this->getRegion(\Request::instance()));

        return view('statsreports.details.peopletransfersummary', compact('teamMemberSummary', 'incomingSummary', 'thisQuarter'));
    }

    public function getCoursesTransferSummary(StatsReport $statsReport)
    {
        $thisWeek = clone $statsReport->reportingDate;

        $globalReport = GlobalReport::reportingDate($thisWeek->subWeek())->first();
        if (!$globalReport) {
            return null;
        }

        $lastStatsReport = $globalReport->getStatsReportByCenter($statsReport->center);
        if (!$lastStatsReport) {
            return null;
        }

        $thisWeekCourses = App::make(CoursesController::class)->getByStatsReport($statsReport);
        $lastWeekCourses = App::make(CoursesController::class)->getByStatsReport($lastStatsReport);

        $a = new Arrangements\CoursesWithEffectiveness([
            'courses' => $thisWeekCourses,
            'reportingDate' => $statsReport->reportingDate,
        ]);
        $thisWeekCoursesList = $a->compose();
        $thisWeekCoursesList = $thisWeekCoursesList['reportData'];

        $a = new Arrangements\CoursesWithEffectiveness([
            'courses' => $lastWeekCourses,
            'reportingDate' => $lastStatsReport->reportingDate,
        ]);
        $lastWeekCoursesList = $a->compose();
        $lastWeekCoursesList = $lastWeekCoursesList['reportData'];

        /*
         * No completed courses copied over (Done by validator)
         * Starting numbers match the completing numbers from last week
         */
        $flaggedCount = 0;
        $flagged = [
            'missing' => [],
            'changed' => [],
            'new' => [],
        ];
        foreach ($thisWeekCoursesList as $type => $thisWeekCourses) {
            foreach ($thisWeekCourses as $thisWeekCourseData) {
                $found = false;
                if (isset($lastWeekCoursesList[$type])) {
                    foreach ($lastWeekCoursesList[$type] as $idx => $lastWeekCourseData) {
                        if ($this->coursesEqual($thisWeekCourseData, $lastWeekCourseData)) {
                            if (!$this->courseCopiedCorrectly($thisWeekCourseData, $lastWeekCourseData)) {
                                $flagged['changed'][$type][] = [$thisWeekCourseData, $lastWeekCourseData];
                                $flaggedCount++;
                            }
                            $found = true;
                            unset($lastWeekCoursesList[$type][$idx]);
                            continue;
                        }
                    }
                }
                if (!$found) {
                    $flagged['new'][$type][] = [$thisWeekCourseData, null];
                    $flaggedCount++;
                }
            }
        }

        unset($lastWeekCoursesList['completed']);

        foreach ($lastWeekCoursesList as $type => $lastWeekCourses) {
            foreach ($lastWeekCourses as $lastWeekCourseData) {
                $flagged['missing'][$type][] = [null, $lastWeekCourseData];
                $flaggedCount++;
            }
        }

        return view('statsreports.details.coursestransfersummary', compact('flagged', 'flaggedCount'));
    }

    protected function coursesEqual($new, $old)
    {
        if ($new['courseId'] == $old['courseId']) {
            return true;
        }

        return $new['startDate']->eq($old['startDate']);
    }

    public function courseCopiedCorrectly($new, $old)
    {
        // Make sure quarter starting numbers match last quarters ending numbers
        if ($new['quarterStartTer'] != $old['currentTer']) {
            return false;
        }
        if ($new['quarterStartStandardStarts'] != $old['currentStandardStarts']) {
            return false;
        }
        if ($new['quarterStartXfer'] != $old['currentXfer']) {
            return false;
        }

        // There wasn't a course during the TMLP weekend, so this must be empty
        if ($new['completedStandardStarts'] !== null) {
            return false;
        }
        if ($new['potentials'] !== null) {
            return false;
        }
        if ($new['registrations'] !== null) {
            return false;
        }

        return true;
    }

    public function incomingCopiedCorrectly($new, $old)
    {
        $checkFields = [
            'regDate',
            'appOutDate',
            'appInDate',
            'apprDate',
            'teamYear',
            'incomingQuarterId',
        ];

        $ok = true;
        foreach ($checkFields as $field) {

            if ($old->$field && $new->$field) {
                if (($old->$field instanceof Carbon) && $old->$field->ne($new->$field)) {
                    $ok = false;
                    break;
                } else if ($old->$field != $new->$field) {
                    $ok = false;
                    break;
                }
            } else if ($old->$field && !$new->$field) {
                $ok = false;
                break;
            }
        }

        return $ok;
    }

    public function getTargetValue($object)
    {
        if ($object instanceof TmlpRegistrationData) {
            return $object->registration->personId;
        } else if ($object instanceof TeamMemberData) {
            return $object->teamMember->personId;
        } else if ($object instanceof CourseData) {
            return $object->courseId;
        }

        return null;
    }

    public function objectsAreEqual($a, $b)
    {
        return $this->getTargetValue($a) == $this->getTargetValue($b);
    }

    public function hasPerson($fields, $needle, $haystacks, $haystackObjectOffset = null)
    {
        foreach ($fields as $field) {
            if (isset($haystacks[$field])) {
                foreach ($haystacks[$field] as $idx => $object) {
                    $target = $haystackObjectOffset === null
                    ? $object
                    : $object[$haystackObjectOffset];

                    if ($this->objectsAreEqual($target, $needle)) {
                        return [$field, $idx, $target];
                    }
                }
            }
        }

        return [null, null, null];
    }

    public static function getUrl(StatsReport $statsReport)
    {
        $abbr = strtolower($statsReport->center->abbreviation);
        $date = $statsReport->reportingDate->toDateString();

        return url("/reports/centers/{$abbr}/{$date}");
    }
}
