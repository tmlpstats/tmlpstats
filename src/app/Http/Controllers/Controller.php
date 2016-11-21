<?php
namespace TmlpStats\Http\Controllers;

use App;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Session;
use TmlpStats\Api;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Region;
use TmlpStats\ReportToken;

class Controller extends BaseController
{
    use ValidatesRequests, AuthorizesRequests;

    protected $context;
    protected $region = null;
    protected $center = null;
    protected $reportingDate = null;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->context = App::make(Api\Context::class);
    }

    /**
     * Get the select region to display content for based on input or save settings
     *
     * @param Request $request
     * @param bool|false $includeLocalRegions
     * @return null
     */
    public function getRegion(Request $request, $includeLocalRegions = false)
    {
        if ($this->region) {
            return $this->region;
        }

        $region = null;
        if ($request->has('region')) {
            $region = Region::abbreviation($request->get('region'))->first();
            Session::set('viewRegionId', $region->id);
        }

        if (!$region && Session::has('viewRegionId')) {
            $region = Region::find(Session::get('viewRegionId'));
        }

        if (!$region && Auth::user()) {
            $region = Auth::user()->homeRegion();
        }

        if (!$region) {
            $region = Region::abbreviation('NA')->first();
        }

        if (!$includeLocalRegions && !$region->isGlobalRegion()) {
            $region = $region->getParentGlobalRegion();
        }

        if ($region) {
            $this->setRegion($region);
        }

        return $region;
    }

    public function getCenter(Request $request)
    {
        if ($this->center) {
            return $this->center;
        }

        $center = null;
        if ($request->has('center')) {
            $center = Center::abbreviation($request->get('center'))->first();
            Session::set('viewCenterId', $center->id);
        }

        if (!$center && Session::has('viewCenterId')) {
            $center = Center::find(Session::get('viewCenterId'));
        }

        if (!$center && Auth::user()) {
            $center = Auth::user()->center;
        }

        if ($center) {
            $this->setCenter($center);
        }

        return $center;
    }

    public function getReportingDate()
    {
        $reportingDate = null;
        $reportingDateString = '';

        // Then try to pull it from the session if it's there
        if (Session::has('viewReportingDate')) {
            $reportingDateString = Session::get('viewReportingDate');
        }

        // If we have a reportToken, use the reportingDate from that report
        if (!$reportingDateString && Session::has('reportTokenId')) {
            $reportToken = ReportToken::find(Session::get('reportTokenId'));

            if ($reportToken) {
                $report = $reportToken->getReport();
                $reportingDateString = $report->reportingDate->toDateString();
                Session::set('viewReportingDate', $reportingDateString);
            }
        }

        // Finally, if we don't have it yet make an educated guess
        if ($reportingDateString) {
            $reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDateString);
        } else {
            $reportingDate = ImportManager::getExpectedReportDate();
        }

        $reportingDate = $reportingDate->startOfDay();

        $this->setReportingDate($reportingDate);

        return $reportingDate;
    }

    public function setRegion(Region $region)
    {
        $this->region = $region;
    }

    public function setCenter(Center $center)
    {
        if ($center) {
            Session::set('viewCenterId', $center->id);
        }

        $this->center = $center;
    }

    public function setReportingDate(Carbon $reportingDate)
    {
        if ($reportingDate) {
            Session::set('viewReportingDate', $reportingDate->toDateString());
        }

        $this->reportingDate = $reportingDate;
    }

    protected function getApi($apiName)
    {
        if (strpos($apiName, '.') !== false) {
            $apiName = str_replace('.', '\\', $apiName);
        }
        return App::make($apiName);
    }
}
