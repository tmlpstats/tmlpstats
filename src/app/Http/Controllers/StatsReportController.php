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
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByIncomingQuarter;
use TmlpStats\Reports\Arrangements\TmlpRegistrationsByStatus;

use Carbon\Carbon;

use App;
use Auth;
use Cache;
use Exception;
use Input;
use Log;
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
    public function index()
    {
        $selectedRegion = $this->getRegion();

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


    public function getSummary($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $centerStatsData = App::make(CenterStatsController::class)->getByStatsReport($id, $statsReport->reportingDate);
        if (!$centerStatsData) {
            return '<p>Center Stats not available.</p>';
        }

        $a = new GamesByWeek($centerStatsData);
        $centerStatsData = $a->compose();

        $date = $statsReport->reportingDate->toDateString();
        $reportData = $centerStatsData['reportData'][$date];

        $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($id);
        $tdo = $teamMembers['tdo'];
        $gitw = $teamMembers['gitw'];
        $teamWithdraws = $teamMembers['withdraws'];

        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($id);

        $a = new TmlpRegistrationsByStatus(['registrationsData' => $registrations, 'quarter' => $statsReport->quarter]);
        $applications = $a->compose();
        $applications = $applications['reportData'];

        $a = new TmlpRegistrationsByIncomingQuarter(['registrationsData' => $registrations, 'quarter' => $statsReport->quarter]);
        $applicationWithdraws = $a->compose();
        $applicationWithdraws = $applicationWithdraws['reportData']['withdrawn'];

        $courses = App::make(CoursesController::class)->getByStatsReport($id);

        $a = new CoursesWithEffectiveness(['courses' => $courses, 'statsReport' => $statsReport]);
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

    public function getCenterStats($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $centerStatsData = App::make(CenterStatsController::class)->getByStatsReport($id);
        if (!$centerStatsData) {
            return '<p>Center Stats not available.</p>';
        }

        $a = new GamesByWeek($centerStatsData);
        $weeklyData = $a->compose();

        $a = new GamesByMilestone(['weeks' => $weeklyData['reportData'], 'quarter' => $statsReport->quarter]);
        $data = $a->compose();

        return view('reports.centergames.milestones', $data);
    }

    public function getTeamMembers($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($id);
        if (!$teamMembers) {
            return '<p>Team Members not available.</p>';
        }


        return view('statsreports.details.classlist', compact(
            'teamMembers'
        ));
    }

    public function getTmlpRegistrationsByStatus($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($id);
        if (!$registrations) {
            return '<p>TMLP Registrations not available.</p>';
        }

        $a = new TmlpRegistrationsByStatus(['registrationsData' => $registrations]);
        $data = $a->compose();

        $data = array_merge($data, ['reportingDate' => $statsReport->reportingDate]);
        return view('statsreports.details.tmlpregistrationsbystatus', $data);
    }

    public function getTmlpRegistrations($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $registrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($id);
        if (!$registrations) {
            return '<p>TMLP Registrations not available.</p>';
        }

        $a = new TmlpRegistrationsByIncomingQuarter(['registrationsData' => $registrations, 'quarter' => $statsReport->quarter]);
        $data = $a->compose();

        return view('statsreports.details.tmlpregistrations', $data);
    }

    public function getCourses($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $courses = App::make(CoursesController::class)->getByStatsReport($id);
        if (!$courses) {
            return '<p>Courses not available.</p>';
        }

        $a = new CoursesWithEffectiveness(['courses' => $courses, 'statsReport' => $statsReport]);
        $data = $a->compose();

        return view('statsreports.details.courses', $data);
    }

    public function getContacts($id)
    {
        $statsReport = StatsReport::find($id);
        if (!$statsReport->isValidated()) {
            return '<p>This report did not pass validation. See Report Details for more information.</p>';
        }

        $contacts = App::make(ContactsController::class)->getByStatsReport($id);
        if (!$contacts) {
            return '<p>Contacts not available.</p>';
        }

        return view('statsreports.details.contactinfo', compact(
            'contacts'
        ));
    }

    public function getResults($id)
    {
        $statsReport = StatsReport::find($id);

        if ($statsReport && !$this->hasAccess($statsReport->center->id, 'R')) {
            $error = 'You do not have access to this report.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        $sheet = array();
        $sheetUrl = '';

        if ($statsReport) {

            $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

            if ($sheetPath) {

                $cacheKey = "statsReport{$id}:validation";
                $sheet = (static::USE_CACHE) ? Cache::tags(["statsReport{$id}"])->get($cacheKey) : false;
                if (!$sheet) {
                    try {
                        $importer = new XlsxImporter($sheetPath, basename($sheetPath), $statsReport->reportingDate, false);
                        $importer->import(false);
                        $sheet = $importer->getResults();
                    } catch (Exception $e) {
                        Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    }
                }
                Cache::tags(["statsReport{$id}"])->put($cacheKey, $sheet, static::STATS_REPORT_CACHE_TTL);

                $sheetUrl = $sheetPath
                    ? url("/statsreports/{$statsReport->id}/download")
                    : null;
            }
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
