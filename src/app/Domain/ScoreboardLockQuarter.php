<?php

namespace TmlpStats\Domain;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Represents the lock settings for an entire quarter; typically this is stored for each center-quarter but it could theoretically also be stored at the region-quarter level.
 */
class ScoreboardLockQuarter implements Arrayable
{
    protected $reportingDates;

    public function __construct($initWeeks = [])
    {
        $this->reportingDates = [];
        foreach ($initWeeks as $week) {
            $this->reportingDates[$week->toDateString()] = collect([]);
        }
    }

    protected function getReportingDate(Carbon $reportingDate): Collection
    {
        $key = $reportingDate->toDateString();
        $v = array_get($this->reportingDates, $key, null);
        if ($v === null) {
            $this->reportingDates[$key] = $v = collect([]);
        }

        return $v;
    }

    /**
     * Return the configuration for a given week.
     * @param  Carbon $week   The friday we care about.
     * @return ScoreboardLock|null Return the lock setting if it exists in this collection, otherwise null.
     */
    public function getWeek(Carbon $reportingDate, Carbon $week)
    {
        $weeks = $this->getReportingDate($reportingDate);

        return $weeks->get($week->toDateString(), null);
    }

    /**
     * like getWeek, except return a week with the default value (all locked down) if the week doesn't exist in this collection.
     */
    public function getWeekDefault(Carbon $reportingDate, Carbon $week): ScoreboardLock
    {
        $collection = $this->getReportingDate($reportingDate);
        $key = $week->toDateString();

        $result = $collection->get($key);
        if ($result === null) {
            $result = new ScoreboardLock($week);
            $collection->put($key, $result);
        }

        return $result;
    }

    public function toArray()
    {
        ksort($this->reportingDates);
        $rds = [];
        foreach ($this->reportingDates as $k => $items) {
            if ($items->count() && $items->contains(function ($k, $v) {return $v->isinteresting();})) {
                $rds[] = [
                    'reportingDate' => $k,
                    'weeks' => $items->values()->toArray(),
                ];
            }
        }

        return ['reportingDates' => $rds];
    }

    public static function fromArray($data): ScoreboardLockQuarter
    {
        $v = new static();
        if (!isset($data['reportingDates'])) {
            return $v;
        }
        foreach ($data['reportingDates'] as $items) {
            $rawReportingDate = $items['reportingDate'];
            $reportingDate = Carbon::createFromFormat('Y-m-d', $rawReportingDate);
            foreach ($items['weeks'] as $weekRaw) {
                $d = Carbon::createFromFormat('Y-m-d', $weekRaw['week']);
                $week = new ScoreboardLock($d);
                $week->editPromise = array_get($weekRaw, 'editPromise', false);
                $week->editActual = array_get($weekRaw, 'editActual', false);
                $v->reportingDates[$rawReportingDate] = $arr = array_get($v->reportingDates, $rawReportingDate, collect([]));
                $arr->put($weekRaw['week'], $week);
            }
        }

        return $v;
    }
}
