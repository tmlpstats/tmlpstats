<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\Scoreboard;
use TmlpStats\StatsReport;

class GamesByWeek extends BaseArrangement
{
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
            foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game) {
                $reportData[$dateString][$type][$game] = $data->$game;

                if ($complement) {
                    if ($type == 'promise') {
                        $percent = Scoreboard::calculatePercent($data->$game, $complement[$game]);
                    } else {
                        $percent = Scoreboard::calculatePercent($complement[$game], $data->$game);
                    }

                    $points = Scoreboard::getPoints($percent, $game);

                    $reportData[$dateString]['percent'][$game] = $percent;
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

    protected function getComplementType($type)
    {
        return ($type === 'promise')
            ? 'actual'
            : 'promise';
    }
}
