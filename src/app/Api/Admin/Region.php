<?php namespace TmlpStats\Api\Admin;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;
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
        $allRqds = Models\RegionQuarterDetails::byRegion($region)->get()->keyBy('quarterId');
        $allQuarters = Models\Quarter::get();
        foreach ($allQuarters as $q) {
            if ($rqd = $allRqds->get($q->id)) {
                $rqd->setRelation('quarter', $q);
                $regionQuarters[] = Encapsulations\RegionQuarter::ensure($region, $rqd->quarter);
            } else if (count($regionQuarters)) {
                // Fake a basic regionQuarter with most info missing.
                $regionQuarters[] = [
                    'id' => "{$region->id}/{$q->id}",
                    'regionId' => $region->id,
                    'quarterId' => $q->id,
                ];
            }
        }

        return [
            'success' => true,
            'region' => $region,
            'centers' => $centers,
            'currentQuarter' => $currentQuarter->toArray()['id'],
            'quarters' => $regionQuarters,
        ];
    }

    public function getQuarterConfig(Models\Region $region, Models\Quarter $quarter)
    {
        $this->assertCan('viewManageUi', $region);
        $rqd = Models\RegionQuarterDetails::byRegion($region)->byQuarter($quarter)->first();
        if ($rqd) {
            $rq = new Encapsulations\RegionQuarter($region, $quarter, $rqd);
            $data = collect($rq->toArray())->except(['id', 'regionId']);
            $data['location'] = $rqd->location;
            $data['travelDueByDate'] = $rq->getTravelDueByDate();
        } else {
            $data = ['location' => ''];
        }
        $data['appRegFutureQuarterWeeks'] = $this->context->getSetting('appRegFutureQuarterWeeks', $region, $quarter) ?? 3;
        $domain = Domain\QuarterConfig::fromArray($data);
        return $domain->toArray();
    }

    public function saveQuarterConfig(Models\Region $region, Models\Quarter $quarter, $data)
    {
        $this->assertCan('viewManageUi', $region);
        $domain = Domain\QuarterConfig::fromArray($data);
        $domain->validate();
        $rqd = Models\RegionQuarterDetails::firstOrNew([
            'region_id' => $region->id,
            'quarter_id' => $quarter->id,
        ]);
        $domain->fillModel($rqd, compact('region', 'quarter'));
    }
}
