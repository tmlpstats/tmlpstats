<?php namespace TmlpStats\Api;

use App;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain\Scoreboard;
use TmlpStats\Domain\ScoreboardGame;
use TmlpStats\Http\Controllers\CenterStatsController;
use TmlpStats\Reports\Arrangements;

class LiveScoreboard extends AuthenticatedApiBase
{

    public function getCurrentScores(Models\Center $center)
    {
        $report = $this->getLatestReport($center);
        $reportData = $this->getOfficialScores($report);
        if (!$reportData) {
            $scoreboard = Scoreboard::blank();
        } else {
            $scoreboard = Scoreboard::fromArray($reportData);
        }

        $tempScores = Models\TempScoreboard::allValidFromCenter($center->id, $report->reportingDate);
        //throw new \Exception("$tempScores");
        // cap, cpc, etc, check for overrides
        $scoreboard->eachGame(function (ScoreboardGame &$game) use ($tempScores) {
            $key = self::key($game->key, 'actual');
            if (array_key_exists($key, $tempScores)) {
                $value = $tempScores[$key]->value;
                $game->setActual($value);
            }
        });
        return $scoreboard;
    }

    public function setScore(Models\Center $center, $game, $type, $value)
    {
        if ($type != 'actual') {
            throw new Exception("Currently, changing promises is not supported.");
        }
        Models\TempScoreboardLog::create([
            'center_id' => $center->id,
            'game' => $game,
            'type' => $type,
            'value' => $value,
        ]);

        $item = Models\TempScoreboard::firstOrNew([
            'center_id' => $center->id,
            'routing_key' => static::key($game, $type),
        ]);
        $item->value = $value;
        $item->save();

        $reportData = $this->getCurrentScores($center)->toArray();
        $reportData['success'] = true;

        return $reportData;
    }

    /** Separated out so that we can easily mock this without having to mock the ORM */
    protected function getLatestReport(Models\Center $center)
    {
        return Models\StatsReport::byCenter($center)->official()->orderBy('reporting_date', 'desc')->first();
    }

    /** Separated out for both abstraction and easy mocking */
    protected function getOfficialScores(Models\StatsReport $report)
    {
        $centerStatsData = App::make(CenterStatsController::class)->getByStatsReport($report, $report->reportingDate);
        if (!$centerStatsData) {
            return null;
        }

        // Center Games
        $a = new Arrangements\GamesByWeek($centerStatsData);
        $centerStatsData = $a->compose();
        $date = $report->reportingDate->toDateString();
        $reportData = $centerStatsData['reportData'][$date];
        return $reportData;
    }

    private static function key($game, $type)
    {
        if ($type != 'promise' && $type != 'actual') {
            throw new Exception("type must be 'promise' or 'actual'");
        }
        return "live.{$game}.{$type}";
    }
}
