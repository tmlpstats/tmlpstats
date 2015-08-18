<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Http\Inputs;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\StatsReport;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Import\Xlsx\XlsxImporter;

use Illuminate\Http\Request;

use Carbon\Carbon;

use Auth;
use Input;
use Log;

class StatsReportController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
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
        $selectedRegion = Input::has('region') ? Input::get('region') : Auth::user()->homeRegion();
        $selectedRegion = $selectedRegion ?: 'NA';

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
                         ->globalRegion($selectedRegion)
                         ->orderBy('local_region', 'asc')
                         ->orderBy('name', 'asc')
                         ->get();

        $statsReportList = array();
        foreach ($centers as $center) {
            $statsReportList[$center->name] = array(
                'center' => $center,
                'report' => StatsReport::reportingDate($reportingDate)
                                       ->orderBy('submitted_at', 'desc')
                                       ->center($center)
                                       ->first(),
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
    public function create() { }

    /**
     * Store a newly created resource in storage.
     *
     * Not used
     *
     * @return Response
     */
    public function store() { }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $statsReport = StatsReport::find($id);
        $sheet = array();

        if ($statsReport) {

            $sheetUrl = ImportManager::getSheetPath($statsReport->reportingDate->toDateString(), $statsReport->center->sheetFilename);

            try {
                $importer = new XlsxImporter($sheetUrl, basename($sheetUrl), $statsReport->reportingDate, false);
                $importer->import(false);
                $sheet = $importer->getResults();
            } catch(Exception $e) {
                Log::error("Error validating sheet: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
        }
        return view('statsreports.show', compact('statsReport', 'sheet'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        echo "Coming soon...";
    }

    /**
     * Update the specified resource in storage.
     *
     * Currently only supports updated locked property
     *
     * @param  int  $id
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

        $locked = Input::get('locked', null);
        if ($locked !== null) {
            $statsReport = StatsReport::find($id);

            if ($statsReport) {
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
                $response['message'] = 'Unable to lock stats report.';
                Log::error("User {$userEmail} attempted to lock statsReport {$id}. Not found.");
            }
        } else {
            $response['message'] = 'Invalid value for locked.';
            Log::error("User {$userEmail} attempted to lock statsReport {$id}. No value provided for locked. ");
        }

        return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $userEmail = Auth::user()->email;
        $success = false;
        $message = '';

        $statsReport = StatsReport::find($id);

        if ($statsReport) {
            if ($statsReport->clear() && $statsReport->delete()) {
                $success = true;
                $message = 'Stats report deleted successfully.';
                Log::info("User {$userEmail} deleted statsReport {$id}");
            } else {
                $message = 'Unable to delete stats report.';
                Log::error("User {$userEmail} attempted to delete statsReport {$id}. Failed to clear or delete.");
            }
        } else {
            $message = 'Unable to delete stats report.';
            Log::error("User {$userEmail} attempted to delete statsReport {$id}. Not found.");
        }

        return ['statsReport' => $id, 'success' => $success, 'message' => $message];
    }
}
