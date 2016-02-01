<?php

namespace TmlpStats\Http\Controllers;

use App;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Session;
use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Http\Requests;
use TmlpStats\Region;
use TmlpStats\ReportToken;
use TmlpStats\StatsReport;

class ReportsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Set active report center to display
     *
     * @param Request $request
     *
     * @return array
     */
    public function setCenter(Request $request)
    {
        if (!$request->has('id') || !is_numeric($request->get('id'))) {
            abort(400);
        }

        $center = Center::findOrFail($request->get('id'));

        Session::set('viewCenterId', $center->id);
        Session::set('reportRedirect', 'center');

        return ['success' => true];
    }

    /**
     * Set active report region to display
     *
     * @param Request $request
     *
     * @return array
     */
    public function setRegion(Request $request)
    {
        if (!$request->has('id') || !is_numeric($request->get('id'))) {
            abort(400);
        }

        $region = Region::findOrFail($request->get('id'));

        Session::set('viewRegionId', $region->id);
        Session::set('reportRedirect', 'region');

        return ['success' => true];
    }

    /**
     * Set active report date to display
     *
     * @param Request $request
     *
     * @return array
     */
    public function setReportingDate(Request $request)
    {
        if (!$request->has('date') || !preg_match('/^\d\d\d\d-\d\d-\d\d$/', $request->get('date'))) {
            abort(400);
        }

        Session::set('viewReportingDate', $request->get('date'));
        Session::set('reportRedirect', 'date');

        return ['success' => true];
    }

    /**
     * Get report by ReportToken::token
     *
     * @param Request $request
     * @param         $token
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getByToken(Request $request, $token)
    {
        $reportToken = ReportToken::token($token)->first();
        if (!$reportToken) {
            abort(404);
        }

        Session::set('reportTokenId', $reportToken->id);

        $reportUrl = $reportToken->getReportPath();

        if ($reportUrl) {
            return redirect($reportUrl);
        } else {
            abort(404);
        }
    }

    /**
     * Get a local report
     *
     * @param Request $request
     * @param         $abbr
     * @param null    $date
     *
     * @return mixed
     */
    public function getCenterReport(Request $request, $abbr = null, $date = null)
    {
        if ($request->has('reportRedirect')) {
            Session::set('reportRedirect', $request->get('reportRedirect'));

            // If the redirect is coming from an HTTP request, then reset the Session variable
            // so we pick up the new version
            if ($abbr && $request->get('reportRedirect') == 'center') {
                Session::forget('viewCenterId');
            }
        }

        if (Session::has('reportRedirect') && Session::get('reportRedirect') == 'region') {
            return $this->getRegionReport($request, $abbr, $date);
        }

        if (!Session::has('reportRedirect') && !$abbr) {
            Session::set('reportRedirect', 'center');
        }

        if (!Session::has('viewCenterId')) {
            $center = $abbr
                ? Center::abbreviation($abbr)->firstOrFail()
                : $this->getCenter($request);
            Session::set('viewCenterId', $center->id);
        }

        return $this->getReport($request, $abbr, $date, 'center', 'viewCenterId');
    }

    /**
     * Get a regional report
     *
     * @param Request $request
     * @param         $abbr
     * @param null    $date
     *
     * @return mixed
     */
    public function getRegionReport(Request $request, $abbr = null, $date = null)
    {
        if ($request->has('reportRedirect')) {
            Session::set('reportRedirect', $request->get('reportRedirect'));

            if ($abbr && $request->get('reportRedirect') == 'region') {
                Session::forget('viewRegionId'); // it will be reset to the requested version below
            }
        }

        if (Session::has('reportRedirect') && Session::get('reportRedirect') == 'center') {
            return $this->getCenterReport($request, $abbr, $date);
        }

        if (!Session::has('reportRedirect') && !$abbr) {
            Session::set('reportRedirect', 'region');
        }

        if (!Session::has('viewRegionId')) {
            $region = $abbr
                ? Region::abbreviation($abbr)->firstOrFail()
                : $this->getRegion($request);
            Session::set('viewRegionId', $region->id);
        }

        return $this->getReport($request, $abbr, $date, 'region', 'viewRegionId');
    }

    protected function getReport(Request $request, $abbr, $date, $reportType, $sessionField)
    {
        $reportingDate = null;

        if ($reportType == 'region') {
            $reportTargetClass = Region::class;
            $controllerClass = GlobalReportController::class;
        } else if ($reportType == 'center') {
            $reportTargetClass = Center::class;
            $controllerClass = StatsReportController::class;
        } else {
            throw new \Exception("Invalid report type '{$reportType}'");
        }

        $reportTarget = null;
        $reportViewUpdate = Session::has('reportRedirect');
        if ($reportViewUpdate) {
            Session::forget('reportRedirect');
            if (Session::has($sessionField)) {
                $reportTarget = $reportTargetClass::find(Session::get($sessionField));
            }
            if (Session::has('viewReportingDate')) {
                $reportingDate = Carbon::parse(Session::get('viewReportingDate'));
            }
        }

        if (!$reportTarget) {
            $reportTarget = $reportTargetClass::abbreviation($abbr)
                                              ->firstOrFail();
        }

        if (!$reportingDate) {
            $reportingDate = $date
                ? Carbon::parse($date)
                : $this->getReportingDate($request);
        }

        $report = null;
        if ($reportType == 'region') {
            $report = GlobalReport::reportingDate($reportingDate)
                                  ->firstOrFail();
            $redirectUrl = App::make(GlobalReportController::class)->getUrl($report, $reportTarget);
        } else {
            $report = StatsReport::byCenter($reportTarget)
                                 ->reportingDate($reportingDate)
                                 ->official()
                                 ->firstOrFail();
            $redirectUrl = App::make(StatsReportController::class)->getUrl($report);
        }

        return $reportViewUpdate
            ? redirect($redirectUrl)
            : App::make($controllerClass)->show($request, $report->id);
    }
}
