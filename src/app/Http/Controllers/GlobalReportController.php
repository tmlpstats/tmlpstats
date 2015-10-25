<?php namespace TmlpStats\Http\Controllers;

use App;
use Request;
use Response;
use TmlpStats\Http\Requests;
use TmlpStats\GlobalReport;
use TmlpStats\Quarter;
use TmlpStats\Region;
use TmlpStats\Reports\Arrangements\GamesByMilestone;
use TmlpStats\Reports\Arrangements\GamesByWeek;
use TmlpStats\StatsReport;
use TmlpStats\Center;
use TmlpStats\Reports\Arrangements;

use Carbon\Carbon;

use Input;
use Auth;

class GlobalReportController extends Controller
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
        if (!$this->hasAccess('R')) {
            $error = 'You do not have access to view these reports.';
            return  Response::view('errors.403', compact('error'), 403);
        }

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
        if (!$this->hasAccess('C')) {
            $error = 'You do not have access to create new reports.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        $reportingDates = array();
        $week = new Carbon('this friday');
        while ($week->gt(Carbon::now()->subWeeks(8))) {
            $reportingDates[$week->toDateString()] = $week->format('F j, Y');
            $week->subWeek();
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
        if (!$this->hasAccess('C')) {
            $error = 'You do not have access to save this report.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        $redirect = '/globalreports';

        if (Input::has('cancel')) {
            return redirect($redirect);
        }

        if (Input::has('reporting_date')) {
            GlobalReport::create(array('reporting_date' => Input::get('reporting_date')));
        }
        return redirect($redirect);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        if (!$this->hasAccess('R')) {
            $error = 'You do not have access to view this report.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        $globalReport = GlobalReport::find($id);
        if (!$globalReport) {
            $error = 'Report not found.';
            return  Response::view('errors.404', compact('error'), 404);
        }

        $region = $this->getRegion(true);

        return view('globalreports.show', compact(
            'globalReport',
            'region'
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
        if (!$this->hasAccess('U')) {
            $error = 'You do not have access to update this report.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        if (!Input::has('cancel')) {

            $globalReport = GlobalReport::find($id);
            if ($globalReport) {

                if (Input::has('center')) {
                    $center = Center::abbreviation(Input::get('center'))->first();
                    $statsReport = StatsReport::reportingDate($globalReport->reportingDate)
                        ->byCenter($center)
                        ->validated(true)
                        ->orderBy('submitted_at', 'desc')
                        ->first();

                    if ($statsReport
                        && !$globalReport->statsReports()->find($statsReport->id)
                        && !$globalReport->statsReports()->byCenter($statsReport->center)->first()
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
                        $id = (int)Input::get('id');
                        $globalReport->statsReports()->detach($id);

                        if (Input::has('dataType') && Input::get('dataType') == 'JSON') {
                            return array('globalReportId' => $id, 'statsReport' => $id, 'success' => true, 'message' => 'Removed stats report successfully.');
                        }
                    }
                }
            }
        }

        $redirect = "/globalreports/{$id}";
        if (Input::has('previous_url')) {
            $redirect = Input::has('previous_url');
        }
        return redirect($redirect);
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

    // This is a really crappy authz. Need to address this properly
    public function hasAccess($permissions)
    {
        switch ($permissions) {
            case 'R':
                return (Auth::user()->hasRole('globalStatistician')
                    || Auth::user()->hasRole('administrator')
                    || Auth::user()->hasRole('localStatistician'));
            case 'U':
            case 'C':
            case 'D':
            default:
                return (Auth::user()->hasRole('globalStatistician')
                    || Auth::user()->hasRole('administrator'));
        }
    }

    public function getStatsReportsNotOnList(GlobalReport $globalReport)
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

    public function getRatingSummary($id)
    {
        if (!$this->hasAccess('R')) {
            $error = 'You do not have access to view this report.';
            return  Response::view('errors.403', compact('error'), 403);
        }

        $globalReport = GlobalReport::find($id);
        if (!$globalReport) {
            $error = 'Report not found.';
            return  Response::view('errors.404', compact('error'), 404);
        }

        $region = $this->getRegion(true);

        $statsReports = $globalReport->statsReports()
            ->validated()
            ->byRegion($region)
            ->get();

        // TODO don't force passing the data in in the future
        $a = new Arrangements\RegionByRating($statsReports);
        $data = $a->compose();
        return view('globalreports.details.ratingsummary', $data);
    }

    public function getRegionalStats($id)
    {
        $region = $this->getRegion(true);
        $globalReport = GlobalReport::find($id);

        $quarter = Quarter::byRegion($region)->date($globalReport->reportingDate)->first();
        $quarter->setRegion($region);

        $globalReportData = App::make(CenterStatsController::class)->getByGlobalReport($id, $region);

        $a = new GamesByWeek($globalReportData);
        $weeklyData = $a->compose();

        $a = new GamesByMilestone(['weeks' => $weeklyData['reportData'], 'quarter' => $quarter]);
        $data = $a->compose();
        return view('reports.centergames.milestones', $data);
    }
}
