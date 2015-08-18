<?php namespace TmlpStats\Http\Controllers;

use TmlpStats\Http\Requests;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\GlobalReport;
use TmlpStats\StatsReport;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Input;

class GlobalReportController extends Controller {


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
        $allReports = StatsReport::currentQuarter(null)
                                 ->groupBy('reporting_date')
                                 ->orderBy('reporting_date', 'desc')
                                 ->get();
        if ($allReports->isEmpty()) {
            $allReports = StatsReport::lastQuarter(null)
                                      ->groupBy('reporting_date')
                                      ->orderBy('reporting_date', 'desc')
                                      ->get();
        }

        $reportingDates = array();
        $friday = new Carbon('this friday');
        $reportingDates[$friday->toDateString()] = $friday->format('F j, Y');
        foreach ($allReports as $report) {
            $dateString = $report->reportingDate->toDateString();
            $reportingDates[$dateString] = $report->reportingDate->format('F j, Y');
        }

        return view('globalreports.create', compact('reportingDates'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $redirect = 'admin/globalreports';

        if (Input::has('cancel')) {
            return redirect($redirect);
        }

        $globalReport = null;
        if (Input::has('reporting_date')) {
            $globalReport = GlobalReport::create(array('reporting_date' => Input::get('reporting_date')));
        }
        return redirect($redirect);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $globalReport = GlobalReport::find($id);

        $statsReports = StatsReport::reportingDate($globalReport->reportingDate)
                                   ->whereNotNull('submitted_at')
                                   ->validated(true)
                                   ->get();

        $centers = array();
        foreach ($statsReports as $statsReport) {
            if ($statsReport->globalReports()->find($globalReport->id)
                || $globalReport->statsReports()->center($statsReport->center)->count() > 0) {
                continue;
            }
            $centers[$statsReport->center->abbreviation] = $statsReport->center->name;
        }
        asort($centers);
        if ($centers) {
            $centers = array_merge(array('default' => 'Add Center'), $centers);
        } else {
            $centers = array('default' => 'No Reports Available');
        }

        return view('globalreports.show', compact('globalReport',
                                                  'centers'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return redirect('admin/globalreports/' . $id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        if (!Input::has('cancel')) {

            $globalReport = GlobalReport::find($id);
            if ($globalReport) {

                if (Input::has('center')) {
                    $center = Center::abbreviation(Input::get('center'))->first();
                    $statsReport = StatsReport::reportingDate($globalReport->reportingDate)
                                              ->orderBy('submitted_at', 'desc')
                                              ->validated(true)
                                              ->center($center)
                                              ->first();

                    if ($statsReport
                        && !$globalReport->statsReports()->find($statsReport->id)
                        && !$globalReport->statsReports()->center($statsReport->center)->first()
                    ) {
                        $globalReport->statsReports()->attach([$statsReport->id]);
                    }
                }
                if (Input::has('locked')) {
                    $locked = Input::get('locked');
                    $globalReport->locked = ($locked == false || $locked === 'false') ? false : true;
                    $success = $globalReport->save();

                    if (Input::has('dataType') && Input::get('dataType') == 'JSON') {
                        return array('globalReportId' => $id, 'locked' => $globalReport->locked, 'success' => $success);
                    }
                }
                if (Input::has('remove')) {
                    if (Input::get('remove') == 'statsreport' && Input::has('id')) {
                        $id = (int) Input::get('id');
                        $globalReport->statsReports()->detach($id);

                        if (Input::has('dataType') && Input::get('dataType') == 'JSON') {
                            return array('globalReportId' => $id, 'statsReport' => $id, 'success' => true, 'message' => 'Removed stats report successfully.');
                        }
                    }
                }
            }
        }

        $redirect = 'admin/globalreports/' . $id;
        if (Input::has('previous_url')) {
            $redirect = Input::has('previous_url');
        }
        return redirect($redirect);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

}
