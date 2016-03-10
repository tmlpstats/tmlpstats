<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\Scoreboard;

class GamesByWeek extends BaseArrangement
{
    protected static $scored_games = Scoreboard::GAME_KEYS;

    /*
     * Builds an array of weekly promise/actual pairs
     * broken down by week
     */
    public function build($centerStatsData)
    {
        $reportData = [];
        foreach ($centerStatsData as $data) {

            if (!$data) {
                continue;
            }
            $type = $data->type;
            $dateString = $data->reportingDate->toDateString();
            $reportData[$dateString][$type] = [];

            $complement = isset($reportData[$dateString][$this->getComplementType($type)])
                ? $reportData[$dateString][$this->getComplementType($type)]
                : null;

            $totalPoints = null;
            foreach (static::$scored_games as $game) {
                // Round game because some reports calculate average game scores and values are provided as floats
                $reportData[$dateString][$type][$game] = round($data->$game);

                if ($complement) {
                    if ($type == 'promise') {
                        $percent = Scoreboard::calculatePercent($data->$game, $complement[$game]);
                    } else {
                        $percent = Scoreboard::calculatePercent($complement[$game], $data->$game);
                    }

                    $points = Scoreboard::getPoints($percent, $game);

                    $reportData[$dateString]['percent'][$game] = round($percent);
                    $reportData[$dateString]['points'][$game] = $points;
                    $totalPoints += $points;
                }
            }
            if ($totalPoints !== null) {
                $reportData[$dateString]['points']['total'] = $totalPoints;
                $reportData[$dateString]['rating'] = Scoreboard::getRating($totalPoints);
            }
        }

        return compact('reportData');
    }

    public static function blankLayout()
    {
        $v = [
            'promise' => [],
            'actual' => [],
            'percent' => [],
            'points' => ['total' => 0],
            'rating' => 'Ineffective',
        ];
        foreach (static::$scored_games as $game) {
            foreach (['promise', 'actual', 'percent', 'points'] as $key) {
                $v[$key][$game] = 0;
            }
        }
        return $v;
    }

    protected function getComplementType($type)
    {
        return ($type === 'promise')
            ? 'actual'
            : 'promise';
    }
}
