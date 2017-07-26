<?php
namespace TmlpStats\Encapsulations\Scopes;

use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Traits\ScopedSettings;

/**
 * RegionQuarter encapsulates data specific to quarters in a region.
 *
 * Use it through the Context class or via the `::ensure` method
 * to allow the context to manage the instantiation and caching of these details.
 */
class RegionQuarterScope
{
    use ScopedSettings;

    protected $region;
    protected $quarter;
    protected $parent = null;

    private $dates = [];

    public function __construct(Models\Region $region, Models\Quarter $quarter, Api\Context $context)
    {
        $this->region = $region;
        $this->quarter = $quarter;
        if ($region->parentId) {
            $this->parent = $context->getEncapsulation(self::class, ['quarter' => $quarter, 'region' => $region->parent]);
        }
    }

    /////// Helpers for RegionQuarter scoped settings.
    protected function scopedSettingBaseQuery()
    {
        return Models\Setting::active()->byRegion($this->region)->byQuarter($this->quarter);
    }

    protected function fetchOneScopedSetting(string $setting)
    {
        $rawSetting = $this->scopedSettingBaseQuery()->name($setting)->first();

        if ($rawSetting !== null) {
            return $rawSetting->value;
        } else if ($this->parent) {
            return $this->parent->getScopedSetting($setting);
        } else {
            return null;
        }
    }

    protected function fetchAllScopedSettings()
    {
        $data = ($this->parent) ? $this->parent->getCachedSettings() : [];
        $settings = $this->scopedSettingBaseQuery()->get();
        foreach ($settings as $rawSetting) {
            $data[$rawSetting->name] = $rawSetting->value;
        }

        return $data;
    }

}
