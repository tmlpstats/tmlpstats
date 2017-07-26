<?php
namespace TmlpStats\Encapsulations\Scopes;

use TmlpStats as Models;
use TmlpStats\Traits\ScopedSettings;

/**
 * This is caching/memoizing primarily for global (non-specific) settings.
 */
class GlobalScope
{
    use ScopedSettings;

    public function __construct()
    {
    }

    protected function scopedSettingBaseQuery()
    {
        return Models\Setting::active()
            ->whereNull('center_id')
            ->whereNull('region_id')
            ->whereNull('quarter_id');
    }
}
