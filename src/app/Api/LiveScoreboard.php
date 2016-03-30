<?php
namespace TmlpStats\Api;

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
    /**
     * Get the current scores
     *
     * @param  Models\Center $center
     * @return array
     */
    public function getCurrentScores(Models\Center $center)
    {
        $report = $this->getLatestReport($center);
        $reportData = $this->getOfficialScores($report);
        if (!$reportData) {
            $scoreboard = Scoreboard::blank();
        } else {
            $scoreboard = Scoreboard::fromArray($reportData);
        }

        $tempScores = $this->getTempScoreboardForCenter($center->id, $report->reportingDate);

        // cap, cpc, etc, check for overrides
        $scoreboard->eachGame(function (ScoreboardGame $game) use ($tempScores) {
            $key = static::key($game->key, 'actual');
            if (isset($tempScores[$key])) {
                $value = $tempScores[$key]->value;
                $game->setActual($value);
            }
        });

        return $scoreboard;
    }

    /**
     * Set score for the provided center and game
     *
     * @param Models\Center $center
     * @param string        $game   Game name (cap, cpc, etc)
     * @param string        $type   Type (actual is the only value supported now)
     * @param integer       $value  Game value
     *
     * @return array
     */
    public function setScore(Models\Center $center, $game, $type, $value)
    {
        if ($type != 'actual') {
            throw new \Exception("Currently, changing promises is not supported.");
        }

        $this->logChanges([
            'center_id' => $center->id,
            'game' => $game,
            'type' => $type,
            'value' => $value,
        ]);

        $item = $this->getTempScoreboardGame([
            'center_id' => $center->id,
            'routing_key' => static::key($game, $type),
        ]);
        $item->value = $value;
        $item->save();

        $reportData = $this->getCurrentScores($center)->toArray();
        $reportData['success'] = true;

        return $reportData;
    }

    /**
     * Get all of the valid temporary scoreboard values for center
     *
     * @param  integer $centerId      Center's id
     * @param  Carbon  $reportingDate Reporting Date as Carbon object
     * @return array
     *
     * @codeCoverageIgnore
     */
    protected function getTempScoreboardForCenter($centerId, Carbon $reportingDate)
    {
        return Models\TempScoreboard::allValidFromCenter($centerId, $reportingDate);
    }

    /**
     * Get temporary scoreboard game, or new one if none exists
     *
     * @param  array  $data
     * @return Models\TempScoreboard
     *
     * @codeCoverageIgnore
     */
    protected function getTempScoreboardGame(array $data)
    {
        return Models\TempScoreboard::firstOrNew($data);
    }

    /**
     * Audit log changes
     *
     * @param  array  $data
     * @return Models\TempScoreboardLog
     *
     * @codeCoverageIgnore
     */
    protected function logChanges(array $data)
    {
        return Models\TempScoreboardLog::create($data);
    }

    /**
     * Get the most recent StatsReport for center
     *
     * @param  Models\Center $center
     * @return Models\StatsReport
     *
     * @codeCoverageIgnore
     */
    protected function getLatestReport(Models\Center $center)
    {
        return Models\StatsReport::byCenter($center)
            ->official()
            ->orderBy('reporting_date', 'desc')
            ->first();
    }

    /**
     * [getOfficialScores description]
     *
     * @param  Models\StatsReport $report [description]
     * @return [type]                     [description]
     */
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
            throw new \Exception("type must be 'promise' or 'actual'");
        }

        return "live.{$game}.{$type}";
    }
}
