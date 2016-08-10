<?php

namespace TmlpStats\Domain;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Represents the lock settings for an entire quarter; typically this is stored for each center-quarter but it could theoretically also be stored at the region-quarter level.
 */
class ScoreboardLockQuarter implements Arrayable
{
    protected $weeks;

    public function __construct()
    {
        $this->weeks = [];
    }

    /**
     * Return the configuration for a given week.
     * @param  Carbon $week   The friday we care about.
     * @return ScoreboardLock Return the lock setting if it exists in this collection, otherwise null.
     */
    public function getWeek(Carbon $week)
    {
        $key = $week->toDateString();

        return array_get($this->weeks, $key, null);
    }

    /**
     * like getWeek, except return a week with the default value (all locked down) if the week doesn't exist in this collection.
     */
    public function getWeekDefault(Carbon $week)
    {
        $result = $this->getWeek($week);
        if ($result === null) {
            $result = new ScoreboardLock($week);
        }

        return $result;
    }

    public function toArray()
    {
        $weeksArray = [];
        ksort($this->weeks);
        foreach ($this->weeks as $v) {
            $weeksArray[] = $v->toArray();
        }

        return ['weeks' => $weeksArray];
    }

    public static function fromArray($data)
    {
        $v = new static();
        foreach ($data['weeks'] as $weekRaw) {
            $d = Carbon::createFromFormat('Y-m-d', $weekRaw['week']);
            $week = new ScoreboardLock($d);
            $week->editPromise = array_get($weekRaw, 'editPromise', false);
            $week->editActual = array_get($weekRaw, 'editActual', false);
            $v->weeks[$weekRaw['week']] = $week;
        }

        return $v;
    }
}
