<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Accountability;
use TmlpStats\CenterStatsData;
use TmlpStats\CourseData;
use TmlpStats\GlobalReport;
use TmlpStats\Http\Inputs;

use TmlpStats\Quarter;
use TmlpStats\Region;
use TmlpStats\StatsReport;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Import\Xlsx\XlsxImporter;
use TmlpStats\Import\Xlsx\XlsxArchiver;

use Carbon\Carbon;

use Auth;
use DB;
use Input;
use Log;
use Response;
use TmlpStats\TeamMemberData;
use TmlpStats\TmlpRegistrationData;

class StatsReportController extends Controller
{
    protected $statsReport = null;

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

        $sheet = array();
        $sheetUrl = '';

        if ($statsReport) {

            $sheetPath = XlsxArchiver::getInstance()->getSheetPath($statsReport);

            if ($sheetPath) {
                $sheetUrl = $sheetPath
                    ? url("/statsreports/{$statsReport->id}/download")
                    : null;
            }

            $this->statsReport = $statsReport;

            // Center Stats
            $week = clone $statsReport->quarter->startWeekendDate;
            $week->addWeek();
            $centerStatsData = array();
            while ($week->lte($statsReport->quarter->endWeekendDate)) {

                if ($week->lte($statsReport->quarter->classroom1Date)) {
                    $classroom = 0;
                } else if ($week->lte($statsReport->quarter->classroom2Date)) {
                    $classroom = 1;
                } else if ($week->lte($statsReport->quarter->classroom3Date)) {
                    $classroom = 2;
                } else {
                    $classroom = 3;
                }

                $centerStatsData[$classroom][$week->toDateString()]['promise'] = $this->getPromiseData($week, $statsReport->center, $statsReport->quarter);

                if ($week->lte($statsReport->reportingDate)) {
                    $centerStatsData[$classroom][$week->toDateString()]['actual'] = $this->getActualData($week, $statsReport->center, $statsReport->quarter);
                }

                $week->addWeek();
            }

            $nextQuarter = $statsReport->quarter->getNextQuarter();

            // Tmlp Registrations
            $tmlpRegistrations = array();
            $registrationsData = TmlpRegistrationData::byStatsReport($statsReport)->with('registration')->get();
            foreach ($registrationsData as $data) {
                if ($data->incomingQuarterId !== $nextQuarter->id) {
                    $tmlpRegistrations['future'][] = $data;
                } else if ($data->registration->teamYear == 1) {
                    $tmlpRegistrations['team1'][] = $data;
                } else {
                    $tmlpRegistrations['team2'][] = $data;
                }
            }

            // Team Members
            $teamMembers = array();
            $memberData = TeamMemberData::byStatsReport($statsReport)->with('teamMember')->get();
            foreach ($memberData as $data) {
                if ($data->teamMember->teamYear == 1) {
                    $teamMembers['team1'][] = $data;
                } else {
                    $teamMembers['team2'][] = $data;
                }
            }

            // Courses
            $courses = array();
            $courseData = CourseData::byStatsReport($statsReport)->with('course')->get();
            foreach ($courseData as $data) {
                if ($data->course->type == 'CAP') {
                    $courses['CAP'][] = $data;
                } else {
                    $courses['CPC'][] = $data;
                }
            }

            // Contacts
            $contacts = array();
            $accountabilities = array(
                'programManager',
                'classroomLeader',
                'team1TeamLeader',
                'team2TeamLeader',
                'teamStatistician',
                'teamStatisticianApprentice',
            );
            foreach ($accountabilities as $accountability) {
                $accountabilityObj = Accountability::name($accountability)->first();
                $contacts[$accountabilityObj->display] = $statsReport->center->getAccountable($accountability);
            }
        }

        return view('statsreports.show', compact(
            'statsReport',
            'sheetUrl',
            'sheet',
            'centerStatsData',
            'tmlpRegistrations',
            'teamMembers',
            'courses',
            'contacts'
        ));
    }


    public function getResults($id)
    {
        $statsReport = StatsReport::find($id);

        if ($statsReport && !$this->hasAccess($statsReport->center->id, 'R')) {
            return 'You do not have access to this report.';
        }

        $sheet = array();
        $sheetUrl = '';

        if ($statsReport) {

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
        }
        $includeUl = true;
        return view('import.results', compact(
            'statsReport',
            'sheetUrl',
            'sheet',
            'includeUl'
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

    // TODO: Refactor this so we're not reusing basically the same code as in importer
    public function getPromiseData(Carbon $date, Center $center, Quarter $quarter)
    {
        $globalReport = null;
        $statsReport = null;

        $firstWeek = clone $quarter->startWeekendDate;
        $firstWeek->addWeek();

        // Usually, promises will be saved in the global report for the expected week
        if ($this->statsReport->reportingDate->gte($quarter->classroom2Date) && $date->gt($quarter->classroom2Date)) {
            $globalReport = GlobalReport::reportingDate($quarter->classroom2Date)->first();
        } else {
            $globalReport = GlobalReport::reportingDate($firstWeek)->first();
        }

        // If there was a global report from those weeks, look there
        if ($globalReport) {
            $statsReport = $globalReport->statsReports()->byCenter($center)->first();
        }

        // It it wasn't found in the expected week, search all weeks from the beginning until
        // we find it
        if (!$statsReport) {
            $statsReport = $this->findFirstWeek($center, $quarter, 'promise');
        }

        // If we can't find one, or if the only one we could find is from this week
        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::promise()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }

    // TODO: Refactor this so we're not reusing basically the same code as in importer
    protected $promiseStatsReport = null;
    public function findFirstWeek(Center $center, Quarter $quarter, $type)
    {
        // Promises should all be saved during the same week. Let's remember where we found the
        // last one.
        if ($this->promiseStatsReport) {
            return $this->promiseStatsReport;
        }

        $statsReportResult = DB::table('stats_reports')
            ->select('stats_reports.id')
            ->join('center_stats_data', 'center_stats_data.stats_report_id', '=', 'stats_reports.id')
            ->join('global_report_stats_report', 'global_report_stats_report.stats_report_id', '=', 'stats_reports.id')
            ->join('global_reports', 'global_reports.id', '=', 'global_report_stats_report.global_report_id')
            ->where('stats_reports.center_id', '=', $center->id)
            ->where('global_reports.reporting_date', '>', $quarter->startWeekendDate)
            ->where('center_stats_data.type', '=', $type)
            ->orderBy('global_reports.reporting_date', 'ASC')
            ->first();

        if ($statsReportResult) {
            $this->promiseStatsReport = StatsReport::find($statsReportResult->id);
        }

        return $this->promiseStatsReport;
    }

    // TODO: Refactor this so we're not reusing basically the same code as in importer
    public function getActualData(Carbon $date, Center $center, Quarter $quarter)
    {
        $statsReport = null;

        // First, check if it's in the official report from the actual date
        $globalReport = GlobalReport::reportingDate($date)->first();
        if ($globalReport) {
            $statsReport = $globalReport->statsReports()->byCenter($center)->first();
        }

        // If not, search from the beginning until we find it
        if (!$statsReport) {
            $statsReport = $this->findFirstWeek($center, $quarter, 'actual');
        }

        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::actual()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }
}
