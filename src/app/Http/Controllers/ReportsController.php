<?php
namespace TmlpStats\Http\Controllers;

use App;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;
use Session;
use TmlpStats\Api;
use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Region;
use TmlpStats\ReportToken;
use TmlpStats\StatsReport;

class ReportsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Only allow token auth for the reports
        $this->middleware('auth.token', ['only' => [
            'getCenterReport',
            'getRegionReport',
        ]]);

        // Require auth everywhere except when we are initially reviewing the reportToken
        $this->middleware('auth', ['except' => [
            'getByToken',
            'mobileDash',
        ]]);
    }

    /**
     * Set active report center to display
     *
     * @param Request $request
     *
     * @return array
     */
    public function setActiveCenter(Request $request)
    {
        if (!$request->has('id') || !is_numeric($request->get('id'))) {
            abort(400);
        }

        $center = Center::findOrFail($request->get('id'));

        $this->setCenter($center);

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
    public function setActiveRegion(Request $request)
    {
        if (!$request->has('id') || !is_numeric($request->get('id'))) {
            abort(400);
        }

        $region = Region::findOrFail($request->get('id'));

        $this->setRegion($region);

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
    public function setActiveReportingDate(Request $request)
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
            Log::info("Token {$reportToken->id} user logged in from " . $request->ip());

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
        if ($reportType == 'region') {
            $reportTargetClass = Region::class;
        } else if ($reportType == 'center') {
            $reportTargetClass = Center::class;
        } else {
            throw new \Exception("Invalid report type '{$reportType}'");
        }

        $reportTarget = null;
        $reportingDate = null;
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
            $reportTarget = $reportTargetClass::abbreviation($abbr)->firstOrFail();
        }

        if (!$reportingDate && $date) {
            $reportingDate = Carbon::parse($date);
        }

        // If we get here and still don't have a reporting date, then none was provided or set in the session
        // Make sure we have an actual report since Controller::getReportingDate() may return the current date
        // even if there hasn't been a report created yet
        $globalReport = null;
        if (!$reportingDate) {
            $reportingDate = $this->context->getReportingDate();

            $globalReport = GlobalReport::reportingDate($reportingDate)->first();
            if (!$globalReport) {
                $reportingDate->subWeek();
                $globalReport = GlobalReport::reportingDate($reportingDate)->first();
            }

            if ($globalReport) {
                Session::set('viewReportingDate', $reportingDate->toDateString());
            }
        }

        $report = null;
        $missingReportReason = null;
        if ($reportType == 'region') {
            $controller = App::make(GlobalReportController::class);

            $report = $globalReport ?: GlobalReport::reportingDate($reportingDate)->first();

            // If we don't have any stats report for this region, then don't show the global report
            if ($report && $report->statsReports()->byRegion($reportTarget)->count() === 0) {
                $report = null;
                $missingReportReason = "There are currently no reports from centers in {$reportTarget->name} for that week.";
            }
        } else {
            $controller = App::make(StatsReportController::class);

            $report = StatsReport::byCenter($reportTarget)
                ->reportingDate($reportingDate)
                ->official()
                ->first();

            if ($report) {
                $this->setCenter($report->center);
            }
        }

        // Find a report from a previous week
        if ($report === null) {
            return $controller->showReportChooser($request, $reportTarget, $reportingDate, $missingReportReason);
        }

        $redirectUrl = $controller->getUrl($report, $reportTarget);

        return $reportViewUpdate
            ? redirect($redirectUrl)
            : $controller->showReport($request, $report, $reportTarget);
    }

    /**
     * The mobile dash summary is an experiment on an auth-less dashboard.
     * Therefore it bypasses all authentication but only for a specific view
     */
    public function mobileDash(Request $request, $abbr)
    {
        $context = App::make(Api\Context::class);
        $center = Center::abbreviation($abbr)->firstOrFail();
        $reportingDate = $context->getReportingDate();

        $this->setCenter($center);

        $report = StatsReport::byCenter($center)->official()->reportingDate($reportingDate)->first();
        if ($report == null) {
            $report = StatsReport::byCenter($center)->official()->orderBy('reporting_date', 'desc')->first();
        }

        $context->setCenter($center);
        $context->setReportingDate($report->reportingDate);

        return App::make(StatsReportController::class)->getMobileSummary($report);
    }
}
