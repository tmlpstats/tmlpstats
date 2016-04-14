<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\Domain\ScoreboardMultiWeek;
use TmlpStats\Scoreboard;

class GamesByWeek extends BaseArrangement
{
    protected static $scoredGames = Scoreboard::GAME_KEYS;

    /*
     * Builds an array of weekly promise/actual pairs
     * broken down by week
     * @return
     *     "2015-05-02" =>
     *         promise =>
     *             cap => 4
     *             cpc => 10,
     *             ...
     *         actual => (same format as promise)
     *         points => (same format as promise, plus "total" key)
     *         rating => "Ineffective"
     */
    public function build($centerStatsData)
    {
        $weeks = new ScoreboardMultiWeek();

        foreach ($centerStatsData as $data) {

            if (!$data) {
                continue;
            }

            $scoreboard = $weeks->ensureWeek($data->reportingDate);

            foreach (static::$scoredGames as $game) {
                // Round game because some reports calculate average game scores and values are provided as floats
                $scoreboard->setValue($game, $data->type, round($data->$game));
            }

        }

        $reportData = $weeks->toArray();

        return compact('reportData');
    }
}
