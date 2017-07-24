<?php
namespace TmlpStats\Traits;

use Carbon\Carbon;
use TmlpStats\Domain\Logic\QuarterDates;
/**
 * Class ParsesQuarterDates
 *
 * Only valid for TmlpStats\Settings
 *
 * @package TmlpStats\Traits
 */
trait ParsesQuarterDates
{
    /**
     * Parse the date field.
     *
     * Values can be any of the following:
     *     classroom1Date, classroom2Date, classroom3Date, endWeekendDate
     *     week1, week2, etc.
     *     An actual date in string format. e.g. 2015-12-31
     *
     * @param $settingValue
     *
     * @return Carbon|null
     * @throws \Exception
     */
    protected function parseQuarterDate($settingValue)
    {
        $center = $this->center;
        $quarter = $this->quarter;

        return QuarterDates::parseQuarterDate($settingValue, $quarter, $center);
    }
}
