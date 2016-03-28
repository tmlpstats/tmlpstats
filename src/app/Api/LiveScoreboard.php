<?php namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
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
        // Step 1: Determine the week for promises to use (may be same week)
        $actualDate = $report->reportingDate;
        $quarterEndDate = $report->quarter->getQuarterEndDate($report->center);
        $promiseWeek = $actualDate->copy();
        while ($promiseWeek->lt(Carbon::now()) && $promiseWeek->lte($quarterEndDate)) {
            $promiseWeek->addWeek();
        }

        // Step 2: Fill an input array with both values, faking them into the same week.
        $csc = App::make(CenterStatsController::class);
        // BUG calling getPromiseData relies on getByStatsReport being called to set a private value it needs
        $csc->getByStatsReport($report);

        $inputData = [];
        $promised = $csc->getPromiseData($promiseWeek, $report->center, $report->quarter);
        if ($promised === null) {
            throw new \Exception("Could not get promise for prom: {$promiseWeek->toDateString()}, actual: {$actualDate->toDateString()}");
        }

        $promised->reportingDate = $report->reportingDate; // Temporarily fake the reporting date
        $inputData[] = $promised;
        $inputData[] = $csc->getActualData($report->reportingDate, $report->center, $report->quarter);

        // Step 3: Use the arrangement to do the work
        $a = new Arrangements\GamesByWeek($inputData);
        $centerStatsData = $a->compose();
        $promised->reportingDate = $promiseWeek; // Set the date back in case it affects our in-memory cache.
        $reportData = $centerStatsData['reportData'][$actualDate->toDateString()];
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
