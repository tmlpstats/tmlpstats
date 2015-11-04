<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Accountability;
use TmlpStats\Center;
use TmlpStats\CenterStatsData;
use TmlpStats\CourseData;
use TmlpStats\GlobalReport;
use TmlpStats\Quarter;
use TmlpStats\StatsReport;
use TmlpStats\TeamMemberData;
use TmlpStats\TmlpRegistrationData;

use TmlpStats\Import\ImportManager;
use TmlpStats\Import\Xlsx\XlsxImporter;
use TmlpStats\Import\Xlsx\XlsxArchiver;

use TmlpStats\Reports\Arrangements\CoursesWithEffectiveness;
use TmlpStats\Reports\Arrangements\GamesByMilestone;
use TmlpStats\Reports\Arrangements\GamesByWeek;
use TmlpStats\Reports\Arrangements\GitwByTeamMember;
use TmlpStats\Reports\Arrangements\TdoByTeamMember;
use TmlpStats\Reports\Arrangements\TeamMembersByQuarter;
use TmlpStats\Reports\Arrangements\TeamMembersCounts;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByIncomingQuarter;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByStatus;

use Carbon\Carbon;

use App;
use Auth;
use Cache;
use Exception;
use Input;
use Log;
use Request;
use Response;

class StatsReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
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
        $reportingDates = array();

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

        $statsReportList = array();
        foreach ($centers as $center) {
            $statsReportList[$center->name] = array(
                'center'   => $center,
                'report'   => StatsReport::reportingDate($reportingDate)
                    ->byCenter($center)
                    ->orderBy('submitted_at', 'desc')
                    ->first(),
                'viewable' => $this->hasAccess($center->id, 'R'),
            );
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
     * @return Response
     */
    public function show($id)
    {
        $statsReport = StatsReport::find($id);

        if ($statsReport && !$this->hasAccess($statsReport->center->id, 'R')) {
            $error = 'You do not have access to this report.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        $sheetUrl = '';
        $globalReport = null;

        if ($statsReport) {

            $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

            if ($sheetPath) {
                $sheetUrl = $sheetPath
                    ? url("/statsreports/{$statsReport->id}/download")
                    : null;
            }

            // Other Stats Reports
            $otherStatsReports = array();
            $searchWeek = clone $statsReport->quarter->startWeekendDate;

            $searchWeek->addWeek();

            while ($searchWeek->lte($statsReport->quarter->endWeekendDate)) {
                $globalReport = GlobalReport::reportingDate($searchWeek)->first();
                if ($globalReport) {
                    $report = $globalReport->statsReports()->byCenter($statsReport->center)->first();
                    if ($report) {
                        $otherStatsReports[$report->id] = $report->reportingDate->format('M d, Y');
                    }
                }
                $searchWeek->addWeek();
            }

            $globalReport = GlobalReport::reportingDate($statsReport->reportingDate)->first();
        }

        return view('statsreports.show', compact(
            'statsReport',
            'globalReport',
            'otherStatsReports',
            'sheetUrl'
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
        return "Coming soon...";
    }

    /**
     * Update the specified resource in storage.
     *
     * Currently only supports updated locked property
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $userEmail = Auth::user()->email;
        $response = array(
            'statsReport' => $id,
            'success'     => false,
            'message'     => '',
        );

        $statsReport = StatsReport::find($id);

        if ($statsReport && !$this->hasAccess($statsReport->center->id, 'U')) {
            $response['message'] = 'You do not have access to update this report.';
            return $response;
        }

        if ($statsReport) {
            $locked = Input::get('locked', null);
            if ($locked !== null) {

                $statsReport->locked = ($locked == false || $locked === 'false') ? false : true;

                if ($statsReport->save()) {
                    $response['success'] = true;
                    $response['locked'] = $statsReport->locked;
                    Log::info("User {$userEmail} " . ($statsReport->locked ? 'locked' : 'unlocked') . " statsReport {$id}");
                } else {
                    $response['message'] = 'Unable to lock stats report.';
                    $response['locked'] = !$statsReport->locked;
                    Log::error("User {$userEmail} attempted to lock statsReport {$id}. Failed to lock.");
                }
            } else {
                $response['message'] = 'Invalid value for locked.';
                Log::error("User {$userEmail} attempted to lock statsReport {$id}. No value provided for locked. ");
            }
        } else {
            $response['message'] = 'Unable to update stats report.';
            Log::error("User {$userEmail} attempted to lock statsReport {$id}. Not found.");
        }

        return $response;
    }

    /**
     * Update the specified resource in storage.
     *
     * Currently only supports updated locked property
     *
     * @param  int $id
     * @return Response
     */
    public function submit($id)
    {
        $userEmail = Auth::user()->email;
        $response = array(
            'statsReport' => $id,
            'success'     => false,
            'message'     => '',
        );

        $statsReport = StatsReport::find($id);

        if ($statsReport) {

            if (!$this->hasAccess($statsReport->center->id, 'U')) {
                $response['message'] = 'You do not have access to submit this report.';
                return $response;
            }

            $action = Input::get('function', null);
            if ($action === 'submit') {

                $sheetUrl = XlsxArchiver::getInstance()->getSheetPath($statsReport);
                $sheet = [];

                try {
                    $importer = new XlsxImporter($sheetUrl, basename($sheetUrl), $statsReport->reportingDate, false);
                    $importer->import(true);
                    $sheet = $importer->getResults();
                } catch (Exception $e) {
                    Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                }

                $statsReport->submittedAt = Carbon::now();
                $statsReport->submitComment = Input::get('comment', null);

                if ($statsReport->save()) {
                    // Cache the validation results so we don't have to regenerate
                    $cacheKey = "statsReport{$id}:validation";
                    Cache::tags(["statsReport{$id}"])->put($cacheKey, $sheet, static::STATS_REPORT_CACHE_TTL);

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
        } else {
            $response['message'] = 'Unable to update stats report.';
            Log::error("User {$userEmail} attempted to submit statsReport {$id}. Not found.");
        }

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $userEmail = Auth::user()->email;
        $response = array(
            'statsReport' => $id,
            'success'     => false,
            'message'     => '',
        );

        $statsReport = StatsReport::find($id);

        if ($statsReport && !$this->hasAccess($statsReport->center->id, 'D')) {
            $response['message'] = 'You do not have access to delete this report.';
            return $response;
        }

        if ($statsReport) {
            if ($statsReport->clear() && $statsReport->delete()) {
                $response['success'] = true;
                $response['message'] = 'Stats report deleted successfully.';
                Log::info("User {$userEmail} deleted statsReport {$id}");
            } else {
                $response['message'] = 'Unable to delete stats report.';
                Log::error("User {$userEmail} attempted to delete statsReport {$id}. Failed to clear or delete.");
            }
        } else {
            $response['message'] = 'Unable to delete stats report.';
            Log::error("User {$userEmail} attempted to delete statsReport {$id}. Not found.");
        }

        return $response;
    }

    public function downloadSheet($id)
    {
        $statsReport = StatsReport::find($id);

        $path = $statsReport
            ? XlsxArchiver::getInstance()->getSheetPath($statsReport)
            : null;

        if ($path) {
            $filename = XlsxArchiver::getInstance()->getDisplayFileName($statsReport);
            return Response::download($path, $filename, [
                'Content-Length: ' . filesize($path),
            ]);
        } else {
            abort(404);
        }
    }

    // This is a really crappy authz. Need to address this properly
    public function hasAccess($centerId, $permissions)
    {
        switch ($permissions) {
            case 'R':
            case 'U':
                return (Auth::user()->hasRole('globalStatistician')
                    || Auth::user()->hasRole('administrator')
                    || (Auth::user()->hasRole('localStatistician') && Auth::user()->center->id === $centerId));
            case 'C':
            case 'D':
            default:
                return (Auth::user()->hasRole('globalStatistician')
                    || Auth::user()->hasRole('administrator'));
        }
    }

    public function runDispatcher(Request $request, $id, $report)
    {
        $statsReport = StatsReport::find($id);

        if (!$statsReport) {
            $error = 'Report not found.';
            return $request->ajax()
                ? "<p>{$error}</p>"
                : Response::view('errors.404', compact('error'), 404);
        }

        if (!$this->hasAccess($statsReport->center->id, 'R')) {
            $error = 'You do not have access to view this report.';
            return $request->ajax()
                ? "<p>{$error}</p>"
                : Response::view('errors.403', compact('error'), 403);
        }

        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $response = null;
        switch ($report) {
            case 'summary':
                $response =  $this->getSummary($statsReport);
                break;
            case 'results':
                $response =  $this->getResults($statsReport);
                break;
            case 'centerstats':
                $response =  $this->getCenterStats($statsReport);
                break;
            case 'classlist':
                $response =  $this->getTeamMembers($statsReport);
                break;
            case 'tmlpregistrations':
                $response =  $this->getTmlpRegistrations($statsReport);
                break;
            case 'tmlpregistrationsbystatus':
                $response =  $this->getTmlpRegistrationsByStatus($statsReport);
                break;
            case 'courses':
                $response =  $this->getCourses($statsReport);
                break;
            case 'contactinfo':
                $response =  $this->getContacts($statsReport);
                break;
            case 'gitwsummary':
                $response =  $this->getGitwByTeamMember($statsReport);
                break;
            case 'tdosummary':
                $response =  $this->getTdoByTeamMember($statsReport);
                break;
        }

        if ($response === null) {
            $error = 'Report not found.';
            $response = $request->ajax()
                ? "<p>{$error}</p>"
                : Response::view('errors.404', $error, 404);
        }

        return $response;
    }

    public function getSummary(StatsReport $statsReport)
    {
        $centerStatsData = App::make(CenterStatsController::class)->getByStatsReport($statsReport->id, $statsReport->reportingDate);
        if (!$centerStatsData) {
            return null;
        }

        $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($statsReport->id);
        if (!$teamMembers) {
            return null;
        }

        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport->id);
        if (!$registrations) {
            return null;
        }

        $courses = App::make(CoursesController::class)->getByStatsReport($statsReport->id);
        if (!$courses) {
            return null;
        }

        # Center Games
        $a = new GamesByWeek($centerStatsData);
        $centerStatsData = $a->compose();

        $date = $statsReport->reportingDate->toDateString();
        $reportData = $centerStatsData['reportData'][$date];

        # Team Member stats
        $a = new TeamMembersCounts(['teamMembersData' => $teamMembers]);
        $teamMembersCounts = $a->compose();

        $tdo = $teamMembersCounts['reportData']['tdo'];
        $gitw = $teamMembersCounts['reportData']['gitw'];
        $teamWithdraws = $teamMembersCounts['reportData']['withdraws'];

        # Application Status
        $a = new TmlpRegistrationsByStatus(['registrationsData' => $registrations, 'quarter' => $statsReport->quarter]);
        $applications = $a->compose();
        $applications = $applications['reportData'];

        # Application Withdraws
        $a = new TmlpRegistrationsByIncomingQuarter(['registrationsData' => $registrations, 'quarter' => $statsReport->quarter]);
        $applicationWithdraws = $a->compose();
        $applicationWithdraws = $applicationWithdraws['reportData']['withdrawn'];

        # Completed Courses
        $a = new CoursesWithEffectiveness(['courses' => $courses, 'reportingDate' => $statsReport->reportingDate]);
        $courses = $a->compose();

        $completedCourses = null;

        if (isset($courses['reportData']['completed'])) {
            $lastWeek = clone $statsReport->reportingDate;
            $lastWeek->subWeek();
            foreach ($courses['reportData']['completed'] as $course) {
                if ($course['startDate']->gte($lastWeek)) {
                    $completedCourses[] = $course;
                }
            }
        }

        return view('statsreports.details.summary', compact(
            'reportData',
            'date',
            'tdo',
            'gitw',
            'applications',
            'teamWithdraws',
            'applicationWithdraws',
            'completedCourses'
        ));
    }

    public function getCenterStats(StatsReport $statsReport)
    {
        $centerStatsData = App::make(CenterStatsController::class)->getByStatsReport($statsReport->id);
        if (!$centerStatsData) {
            return null;
        }

        $a = new GamesByWeek($centerStatsData);
        $weeklyData = $a->compose();

        $a = new GamesByMilestone(['weeks' => $weeklyData['reportData'], 'quarter' => $statsReport->quarter]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    public function getTeamMembers(StatsReport $statsReport)
    {
        $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($statsReport->id);
        if (!$teamMembers) {
            return null;
        }

        $a = new TeamMembersByQuarter(['teamMembersData' => $teamMembers]);
        $data = $a->compose();

        return view('statsreports.details.classlist', $data);
    }

    public function getTmlpRegistrationsByStatus(StatsReport $statsReport)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport->id);
        if (!$registrations) {
            return null;
        }

        $a = new TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $data = $a->compose();

        $data = array_merge($data, ['reportingDate' => $statsReport->reportingDate]);
        return view('statsreports.details.tmlpregistrationsbystatus', $data);
    }

    public function getTmlpRegistrations(StatsReport $statsReport)
    {
        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($statsReport->id);
        if (!$registrations) {
            return null;
        }

        $a = new TmlpRegistrationsByIncomingQuarter(['registrationsData' => $registrations, 'quarter' => $statsReport->quarter]);
        $data = $a->compose();

        return view('statsreports.details.tmlpregistrations', $data);
    }

    public function getCourses(StatsReport $statsReport)
    {
        $courses = App::make(CoursesController::class)->getByStatsReport($statsReport->id);
        if (!$courses) {
            return null;
        }

        $a = new CoursesWithEffectiveness(['courses' => $courses, 'reportingDate' => $statsReport->reportingDate]);
        $data = $a->compose();

        return view('statsreports.details.courses', $data);
    }

    public function getContacts(StatsReport $statsReport)
    {
        $contacts = App::make(ContactsController::class)->getByStatsReport($statsReport->id);
        if (!$contacts) {
            return null;
        }

        return view('statsreports.details.contactinfo', compact(
            'contacts'
        ));
    }

    public function getGitwByTeamMember(StatsReport $statsReport)
    {
        $weeksData = [];

        $date = clone $statsReport->quarter->startWeekendDate;
        $date->addWeek();
        while ($date->lte($statsReport->reportingDate)) {
            $globalReport = GlobalReport::reportingDate($date)->first();
            $report = $globalReport->statsReports()->byCenter($statsReport->center)->first();
            $weeksData[$date->toDateString()] = App::make(TeamMembersController::class)->getByStatsReport($report->id);
            $date->addWeek();
        }
        if (!$weeksData) {
            return null;
        }

        $a = new GitwByTeamMember(['teamMembersData' => $weeksData]);
        $data = $a->compose();

        return view('statsreports.details.teammembersweekly', $data);
    }

    public function getTdoByTeamMember(StatsReport $statsReport)
    {
        $weeksData = [];

        $date = clone $statsReport->quarter->startWeekendDate;
        $date->addWeek();
        while ($date->lte($statsReport->reportingDate)) {
            $globalReport = GlobalReport::reportingDate($date)->first();
            $report = $globalReport->statsReports()->byCenter($statsReport->center)->first();
            $weeksData[$date->toDateString()] = App::make(TeamMembersController::class)->getByStatsReport($report->id);
            $date->addWeek();
        }
        if (!$weeksData) {
            return null;
        }

        $a = new TdoByTeamMember(['teamMembersData' => $weeksData]);
        $data = $a->compose();

        return view('statsreports.details.teammembersweekly', $data);
    }

    public function getResults(StatsReport $statsReport)
    {
        $sheet = array();
        $sheetUrl = '';

        $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

        if ($sheetPath) {

            $cacheKey = "statsReport{$statsReport->id}:validation";
            $sheet = ($this->useCache()) ? Cache::tags(["statsReport{$statsReport->id}"])->get($cacheKey) : false;
            if (!$sheet) {
                try {
                    $importer = new XlsxImporter($sheetPath, basename($sheetPath), $statsReport->reportingDate, false);
                    $importer->import(false);
                    $sheet = $importer->getResults();
                } catch (Exception $e) {
                    Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                }
            }
            Cache::tags(["statsReport{$statsReport->id}"])->put($cacheKey, $sheet, static::STATS_REPORT_CACHE_TTL);

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
}
