<?php
namespace TmlpStats\Encapsulations\Scopes;

use TmlpStats as Models;
use TmlpStats\Traits\ScopedSettings;

/**
 * This is caching/memoizing for center-quarter settings.
 */
class CenterQuarterScope
{
    use ScopedSettings;

    public $center;
    public $quarter;

    public function __construct(Models\Center $center, Models\Quarter $quarter)
    {
        $this->center = $center;
        $this->quarter = $quarter;
    }

    /////// Helpers for CenterQuarter scoped settings.
    protected function scopedSettingBaseQuery()
    {
        return Models\Setting::active()->byCenter($this->center)->byQuarter($this->quarter);
    }

}
