<?php
namespace TmlpStats\Encapsulations\Scopes;

use TmlpStats as Models;
use TmlpStats\Traits\ScopedSettings;

/**
 * This is caching/memoizing primarily for center-specific settings.
 */
class CenterScope
{
    use ScopedSettings;

    public $center;

    public function __construct(Models\Center $center)
    {
        $this->center = $center;
    }

    protected function scopedSettingBaseQuery()
    {
        return Models\Setting::active()->whereNull('quarter_id')->byCenter($this->center);
    }
}
