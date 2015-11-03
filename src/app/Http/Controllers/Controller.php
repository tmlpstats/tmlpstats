<?php
namespace TmlpStats\Http\Controllers;

use Auth;
use Illuminate\Foundation\Bus\DispatchesCommands;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Request;
use Session;
use TmlpStats\Region;

abstract class Controller extends BaseController {

    use DispatchesCommands, ValidatesRequests;

    const CACHE_TTL = 60;
    const STATS_REPORT_CACHE_TTL = 7 * 24 * 60;
    const USE_CACHE = true;

    public function getRegion($includeLocalRegions = false)
    {
        $region = null;
        if (Request::has('region')) {
            $region = Region::abbreviation(Request::get('region'))->first();
            Session::set('viewRegionId', $region->id);
        }

        if (!$region) {
            if (Session::has('viewRegionId')) {
                $region = Region::find(Session::get('viewRegionId'));
            }
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

        return $region;
    }

    public function useCache()
    {
        return env('REPORTS_USE_CACHE', static::USE_CACHE);
    }
}
