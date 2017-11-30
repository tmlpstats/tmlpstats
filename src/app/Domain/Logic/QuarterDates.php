<?php
namespace TmlpStats\Domain\Logic;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Domain;

class QuarterDates
{
    const MARKER_DATE_STR = '1900-01-01';
    static $MARKER_DATE; // gets initialized by initializeVars

    const SIMPLE_DATE_FIELDS = [
        'startWeekendDate',
        'endWeekendDate',
        'classroom1Date',
        'classroom2Date',
        'classroom3Date',
    ];

    /**
     * If date input is a date, nullify it if it's before the marker date.
     * @param  [type] $d [description]
     * @return [type]    [description]
     */
    public static function fixDateInput($d)
    {
        if ($d === null) {
            return null;
        } else {
            if ($d->lt(static::$MARKER_DATE)) {
                return null;
            }
        }

        return $d;
    }

    public static function formatDate($d)
    {
        return ($d !== null) ? $d->toDateString() : null;
    }

    public static function initializeVars()
    {
        static::$MARKER_DATE = Carbon::parse(static::MARKER_DATE_STR);
    }

    /**
     * * Parse the date field.
     *
     * Values can be any of the following:
     *     classroom1Date, classroom2Date, classroom3Date, endWeekendDate
     *     week1, week2, etc.
     *     An actual date in string format. e.g. 2015-12-31
     * @param  string $settingValue The value we're parsing, usually from a setting.
     * @param  mixed  $quarterLike  A quarter-like object (CenterQuarter, RegionQuarter)
     * @param  Center $center       Needed if $quarterLike is just a plain Quarter.
     * @return Carbon
     */
    public static function parseQuarterDate($settingValue, $quarterLike, Models\Center $center = null)
    {
        if (!$quarterLike) {
            throw new \Exception('No quarter-like object provided');
        }

        if ($quarterLike instanceof Models\Quarter) {
            if ($center) {
                $quarterLike = Domain\CenterQuarter::ensure($center, $quarterLike);
            } else {
                // This scenario should only happen in unit tests, but soon we'll log these legacy scenarios.
                $quarterLike = $quarterLike->legacyGetRegionQuarter();
            }
        }

        $quarterDates = [
            'classroom1Date',
            'classroom2Date',
            'classroom3Date',
            'endWeekendDate',
        ];

        if (in_array($settingValue, $quarterDates)) {
            return $quarterLike->$settingValue;
        }

        if (preg_match('/^week(\d+)$/', $settingValue, $matches)) {
            $offsetWeeks = $matches[1];
            $quarterStartDate = $quarterLike->startWeekendDate;

            return $quarterStartDate->copy()->addWeeks($offsetWeeks);
        }

        if (preg_match('/^20\d\d-\d\d-\d\d$/', $settingValue)) {
            return Carbon::parse($settingValue);
        }

        throw new \Exception("Invalid date format: {$settingValue}");
    }

    /**
     * Get the next milestone after the given date 'now'
     *
     * @param  mixed       $quarterLike  A quarter-like object (CenterQuarter, RegionQuarter)
     * @param  Carbon|null $now          The reference date to go off of
     * @return Carbon                    The next milestone date.
     */
    public static function getNextMilestone($quarterLike, Carbon $now = null): Carbon
    {
        $now = $now ?: Carbon::now();

        if ($now->gt($quarterLike->classroom3Date)) {
            return $quarterLike->endWeekendDate;
        } else if ($now->gt($quarterLike->classroom2Date)) {
            return $quarterLike->classroom3Date;
        } else if ($now->gt($quarterLike->classroom1Date)) {
            return $quarterLike->classroom2Date;
        } else {
            return $quarterLike->classroom1Date;
        }
    }
}

// Do initialization here of variables we can't normally initialize
QuarterDates::initializeVars();
