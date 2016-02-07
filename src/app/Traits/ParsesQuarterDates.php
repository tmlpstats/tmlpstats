<?php
namespace TmlpStats\Traits;

use Carbon\Carbon;

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
        $center  = $this->center;
        $quarter = $this->quarter;

        if (!$quarter) {
            throw new \Exception('No quarter provided');
        }

        $quarterDates = [
            'classroom1Date',
            'classroom2Date',
            'classroom3Date',
            'endWeekendDate',
        ];

        if (in_array($settingValue, $quarterDates)) {
            return $quarter->getQuarterDate($settingValue, $center);
        }

        if (preg_match('/^week(\d+)$/', $settingValue, $matches)) {
            $offsetWeeks      = $matches[1];
            $quarterStartDate = $quarter->getQuarterStartDate($center);

            return $quarterStartDate->copy()->addWeeks($offsetWeeks);
        }

        if (preg_match('/^20\d\d-\d\d-\d\d$/', $settingValue)) {
            return Carbon::parse($settingValue);
        }

        throw new \Exception("Invalid date format in setting {$this->setting->id}: {$settingValue}");
    }
}
