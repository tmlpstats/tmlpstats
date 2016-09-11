<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain;

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
            $scoreboard = Domain\Scoreboard::blank();
        } else {
            $scoreboard = Domain\Scoreboard::fromArray($reportData);
        }

        $tempScores = $this->getTempScoreboardForCenter($center->id, $report->reportingDate);
        $updatedAt = $report->submittedAt;

        // cap, cpc, etc, check for overrides
        foreach ($scoreboard->games() as $gameKey => $game) {
            $key = static::key($game->key, 'actual');
            if (isset($tempScores[$key])) {
                $score = $tempScores[$key];
                if ($updatedAt == null || $score->updatedAt->gt($updatedAt)) {
                    $updatedAt = $score->updatedAt;
                }
                $game->setActual($score->value);
            }
        }
        $scoreboard->meta['updatedAt'] = $updatedAt;

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
            throw new \Exception('Currently, changing promises is not supported.');
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
     * Get the scoreboard for given report
     *
     * @param  Models\StatsReport $report
     * @return array
     */
    protected function getOfficialScores(Models\StatsReport $report)
    {
        // Create a fake "now" using UTC so that date comparisons work
        $localNow = Carbon::now($report->center->timezone);
        $now = Carbon::parse($localNow->toDateString(), 'UTC')->startOfDay();

        // Step 1: Determine the week for promises to use (may be same week)
        $actualDate = $report->reportingDate;
        if ($actualDate->lt($now)) {
            $reportingDates = $report->quarter->listReportingDates($report->center);
            foreach ($reportingDates as $promiseDate) {
                if ($promiseDate->gt($now)) {
                    break;
                }
            }
        } else {
            $promiseDate = $actualDate;
        }

        // Step 2: Fill an input array with both values, faking them into the same week.
        if ($actualDate->ne($promiseDate)) {
            $quarterData = App::make(LocalReport::class)->getQuarterScoreboard($report, [
                'returnObject' => true,
            ]);

            $actualWeekData = $quarterData->getWeek($actualDate);
            $promiseWeekData = $quarterData->getWeek($promiseDate);

            if (!isset($promiseWeekData)) {
                throw new \Exception("Could not get promise for promise: {$promiseDate->toDateString()}, actual: {$actualDate->toDateString()}");
            }
            if (!isset($actualWeekData)) {
                throw new \Exception("Could not get actual for promise: {$promiseDate->toDateString()}, actual: {$actualDate->toDateString()}");
            }

            $target = Domain\Scoreboard::blank($promiseDate);
            foreach ($target->games() as $gameKey => $g) {
                $g->setPromise($promiseWeekData->game($gameKey)->promise());
                $g->setActual($actualWeekData->game($gameKey)->actual());
            }

            $reportData = $target->toArray();
        } else {
            // Step 3: LocalReport already did the work for us
            $reportData = App::make(LocalReport::class)->getWeekScoreboard($report);
        }

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

    private static function key($game, $type)
    {
        if ($type != 'promise' && $type != 'actual') {
            throw new \Exception("type must be 'promise' or 'actual'");
        }

        return "live.{$game}.{$type}";
    }
}
