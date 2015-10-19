<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Http\Inputs;

use TmlpStats\Accountability;
use TmlpStats\Center;
use TmlpStats\CenterStatsData;
use TmlpStats\CourseData;
use TmlpStats\GlobalReport;
use TmlpStats\Quarter;
use TmlpStats\Region;
use TmlpStats\StatsReport;
use TmlpStats\TeamMemberData;
use TmlpStats\TmlpRegistrationData;

use TmlpStats\Import\ImportManager;
use TmlpStats\Import\Xlsx\XlsxImporter;
use TmlpStats\Import\Xlsx\XlsxArchiver;

use Carbon\Carbon;

use App;
use Auth;
use Cache;
use Input;
use Log;
use Response;
use View;

class StatsReportController extends Controller
{
    const CACHE_TTL = 60;
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
        $userRegion = Input::has('region')
            ? Region::abbreviation(Input::get('region'))->first()
            : null;
        $selectedRegion = $userRegion ?: Auth::user()->homeRegion();
        $selectedRegion = $selectedRegion ?: Region::abbreviation('NA')->first();

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

        return view('statsreports.index', compact('statsReportList',
            'reportingDates',
            'reportingDate',
            'selectedRegion'));
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
            return 'You do not have access to this report.';
        }

        $sheetUrl = '';

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
        }

        return view('statsreports.show', compact(
            'statsReport',
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

                    XlsxArchiver::getInstance()->promoteWorkingSheet($statsReport);

                    $submittedAt = clone $statsReport->submittedAt;
                    $submittedAt->setTimezone($statsReport->center->timezone);

                    $response['submittedAt'] = $submittedAt->format('h:i A');
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


    public function getCenterStats($id)
    {
        $cacheKey = 'statsReport:centerstats:' . $id;
        $view = Cache::get($cacheKey);

        if (!$view) {
            $centerStatsData = App::make(CenterStatsController::class)->getByStatsReport($id);

            if (!$centerStatsData) {
                return '<p>Center Stats not available.</p>';
            }

            $view = View::make('statsreports.details.centerstats', compact(
                'centerStatsData'
            ))->render();
        }
        Cache::put($cacheKey, $view, static::CACHE_TTL);

        return $view;
    }

    public function getTeamMembers($id)
    {
        $cacheKey = 'statsReport:classlist:' . $id;
        $view = Cache::get($cacheKey);

        if (!$view) {
            $teamMembers = App::make(TeamMembersController::class)->getByStatsReport($id);

            if (!$teamMembers) {
                return '<p>Team Members not available.</p>';
            }

            $view = View::make('statsreports.details.classlist', compact(
                'teamMembers'
            ))->render();
        }
        Cache::put($cacheKey, $view, static::CACHE_TTL);

        return $view;
    }

    public function getTmlpRegistrations($id)
    {
        $cacheKey = 'statsReport:tmlpregistrations:' . $id;
        $view = Cache::get($cacheKey);

        if (!$view) {
            $tmlpRegistrations = App::make(TmlpRegistrationsController::class)->getByStatsReport($id);

            if (!$tmlpRegistrations) {
                return '<p>TMLP Registrations not available.</p>';
            }

            $view = View::make('statsreports.details.tmlpregistrations', compact(
                'tmlpRegistrations'
            ))->render();
        }
        Cache::put($cacheKey, $view, static::CACHE_TTL);

        return $view;
    }

    public function getCourses($id)
    {
        $cacheKey = 'statsReport:courses:' . $id;
        $view = Cache::get($cacheKey);

        if (!$view) {
            $courses = App::make(CoursesController::class)->getByStatsReport($id);

            if (!$courses) {
                return '<p>Courses not available.</p>';
            }

            $view = View::make('statsreports.details.courses', compact(
                'courses'
            ))->render();
        }
        Cache::put($cacheKey, $view, static::CACHE_TTL);

        return $view;
    }

    public function getContacts($id)
    {
        $cacheKey = 'statsReport:contact:' . $id;
        $view = Cache::get($cacheKey);

        if (!$view) {
            $contacts = App::make(ContactsController::class)->getByStatsReport($id);

            if (!$contacts) {
                $view =  '<p>Contacts not available.</p>';
            } else {
                $view = View::make('statsreports.details.contactinfo', compact(
                    'contacts'
                ))->render();
            }
        }
        Cache::put($cacheKey, $view, static::CACHE_TTL);

        return $view;
    }

    public function getResults($id)
    {
        $statsReport = StatsReport::find($id);

        if ($statsReport && !$this->hasAccess($statsReport->center->id, 'R')) {
            return '<p>You do not have access to this report.</p>';
        }

        $sheet = array();
        $sheetUrl = '';

        if ($statsReport) {

            $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

            if ($sheetPath) {

                $cacheKey = 'statsReport:validation:' . sha1($sheetPath);
                $sheet = Cache::get($cacheKey);
                if (!$sheet) {
                    try {
                        $importer = new XlsxImporter($sheetPath, basename($sheetPath), $statsReport->reportingDate, false);
                        $importer->import(false);
                        $sheet = $importer->getResults();
                    } catch (Exception $e) {
                        Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
                    }
                }
                Cache::put($cacheKey, $sheet, static::CACHE_TTL);

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
