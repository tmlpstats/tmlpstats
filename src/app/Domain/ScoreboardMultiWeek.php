<?php
namespace TmlpStats\Domain;

use Carbon\Carbon;

/**
 * A little bonus
 *
 * A container for a scoreboard consisting of multiple weeks.
 * This can add any number of features in the future.
 */
class ScoreboardMultiWeek
{
    protected $weeks = [];

    public static function fromArray($data)
    {
        $result = new static();
        foreach ($data as $weekKey => $weekData) {
            $d = Carbon::createFromFormat('Y-m-d', $weekKey);
            $week = $result->ensureWeek($weekKey);
            $week->parseArray($weekData);
        }

        return $result;
    }

    public function ensureWeek(Carbon $day)
    {
        $key = $day->toDateString();
        if (!isset($this->weeks[$key])) {
            $this->weeks[$key] = Scoreboard::blank();
        }

        return $this->weeks[$key];
    }

    /**
     * Return all in the "canonical format"
     * @return [type] [description]
     */
    public function toArray()
    {
        $output = [];
        $weeks = $this->weeks;
        ksort($weeks);
        foreach ($weeks as $key => $scoreboard) {
            $output[$key] = $scoreboard->toArray();
        }

        return $output;
    }
}
