<?php
namespace TmlpStats\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Region;

use Auth;
use Session;

class Controller extends BaseController
{
    use ValidatesRequests, AuthorizesRequests;

    protected $region = null;
    protected $center = null;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
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

        return $this->region = $region;
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

        return $this->center = $center;
    }

    public function getReportingDate(Request $request, $reportingDates = [])
    {
        $reportingDate = null;
        $reportingDateString = '';
        if ($request->has('reportingDate') && preg_match('/^\d\d\d\d-\d\d-\d\d$/', $request->get('reportingDate'))) {
            $reportingDateString = $request->get('reportingDate');
            Session::set('viewReportingDate', $reportingDateString);
        }

        if (!$reportingDateString && Session::has('viewReportingDate')) {
            $reportingDateString = Session::get('viewReportingDate');
        }

        if ($reportingDateString && in_array($reportingDateString, $reportingDates)) {
            $reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDateString);
        } else if (!$reportingDateString && $reportingDates) {
            $reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDates[0]);
        } else if ($reportingDateString && !$reportingDates) {
            $reportingDate = Carbon::createFromFormat('Y-m-d', $reportingDateString);
        } else {
            $reportingDate = ImportManager::getExpectedReportDate();
        }

        return $reportingDate->startOfDay();
    }
}
