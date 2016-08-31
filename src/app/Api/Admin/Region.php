<?php namespace TmlpStats\Api\Admin;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;

class Region extends AuthenticatedApiBase
{
    public function getRegion(Models\Region $region, $lookups = [])
    {
        // this is a magic property so we can't get it with ->load
        $centers = $region->centers;

        $currentQuarter = Models\Quarter::getQuarterByDate(Carbon::now(), $region);

        return [
            'success' => true,
            'region' => $region,
            'centers' => $centers,
            'currentQuarter' => $currentQuarter,
        ];
    }
}
