<?php
namespace TmlpStats\Domain\Logic;

use Carbon\Carbon;

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
}

// Do initialization here of variables we can't normally initialize
QuarterDates::initializeVars();
