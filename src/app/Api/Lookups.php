<?php
namespace TmlpStats\Api;

use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;

class Lookups extends AuthenticatedApiBase
{
    public function getRegionCenters(Models\Region $region)
    {
        $context = $this->context;
        $this->assertAuthz($context->can('viewManageUi', $region) || $context->can('showReportNavLinks', Models\StatsReport::class));

        return collect($region->centers)->keyBy('id');
    }
}
