<?php
namespace TmlpStats\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use TmlpStats\Import\ImportManager;
use TmlpStats\Region;

use Auth;
use Session;

abstract class Controller extends BaseController
{
    use ValidatesRequests, AuthorizesRequests;

    protected $region = null;

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

        if (!$region) {
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

    public function getReportingDate(Request $request, $reportingDates = [])
    {
        $reportingDate = null;
        $reportingDateString = '';
        if ($request->has('reportingDate')) {
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
        } else {
            $reportingDate = ImportManager::getExpectedReportDate();
        }

        return $reportingDate->startOfDay();
    }
}
