<?php namespace TmlpStats\Api\Admin;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Encapsulations;

class Region extends AuthenticatedApiBase
{
    public function getRegion(Models\Region $region, $lookups = [])
    {
        $this->assertCan('viewManageUi', $region);

        // this is a magic property so we can't get it with ->load
        $centers = $region->centers;

        $cq = Models\Quarter::getQuarterByDate(Carbon::now(), $region);
        $currentQuarter = Encapsulations\RegionQuarter::ensure($region, $cq);

        $regionQuarters = [];
        $allRqds = Models\RegionQuarterDetails::byRegion($region)->get();
        foreach ($allRqds as $rqd) {
            // todo find a neat way to reuse the RQD
            $regionQuarters[] = Encapsulations\RegionQuarter::ensure($region, $rqd->quarter);
        }

        return [
            'success' => true,
            'region' => $region,
            'centers' => $centers,
            'currentQuarter' => $currentQuarter->toArray()['id'],
            'quarters' => $regionQuarters,
        ];
    }
}
