<?php
namespace TmlpStats\Encapsulations\Scopes;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Traits\ScopedSettings;

/**
 * This is caching/memoizing primarily for region-specific settings.
 */
class RegionScope
{
    use ScopedSettings;

    public $region;
    public $parent;

    public function __construct(Models\Region $region)
    {
        $this->region = $region;
        $this->parent = ($region->parentId) ? static::ensure($region->parent) : null;
    }

    public function ensure(Models\Region $region)
    {
        return App::make(Api\Context::class)->getEncapsulation(self::class, compact('region'));
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

    protected function scopedSettingBaseQuery()
    {
        return Models\Setting::active()->byRegion($this->region)->whereNull('quarter_id');
    }

}
