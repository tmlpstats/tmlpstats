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

    public function ensureWeek(Carbon $day)
    {
        $key = $day->toDateString();
        if (!array_key_exists($key, $this->weeks)) {
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
        foreach ($weeks as $key => &$scoreboard) {
            $output[$key] = $scoreboard->toArray();
        }
        return $output;
    }
}
