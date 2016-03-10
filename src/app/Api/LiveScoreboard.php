<?php namespace TmlpStats\Api;

use App;
use Illuminate\Contracts\Cache\Repository;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Http\Controllers\CenterStatsController;
use TmlpStats\Reports\Arrangements;

class LiveScoreboard extends AuthenticatedApiBase
{
    const CACHE_TIMEOUT = 5; // minutes
    protected $cache = null;

    public function __construct(Models\User $user, Repository $cache)
    {
        parent::__construct($user);
        $this->cache = $cache;
    }

    public function getCurrentScores(Models\Center $center)
    {
        $report = $this->getLatestReport($center);
        // BUG FIXME this currently returns last weeks scores. Current scores should really be for current week, or optionally be configurable.
        $reportData = $this->getOfficialScores($report);
        if (!$reportData) {
            // fill with an empty structure
            $reportData = Arrangements\GamesByWeek::blankLayout();
        }

        $cache = $this->cache;

        // cap, cpc, etc, check for overrides
        foreach (Models\Scoreboard::GAME_KEYS as $game) {
            $value = $cache->get($this->key($center, $game, 'actual'));
            if ($value) {
                $reportData['actual'][$game] = intval($value);
            }
        }
        // TODO re-calculate points, etc (Probably going to write a class to do this)
        return $reportData;
    }

    public function setScore(Models\Center $center, $game, $type, $value)
    {
        if ($type != 'actual') {
            throw new Exception("Currently, changing promises is not supported.");
        }
        $this->cache->put($this->key($center, $game, $type), $value, self::CACHE_TIMEOUT);
        return ['success' => true];
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

    private function key($center, $game, $type)
    {
        if ($type != 'promise' && $type != 'actual') {
            throw new Exception("type must be 'promise' or 'actual'");
        }
        return "temp_score.{$center->id}.{$game}.{$type}";
    }
}
