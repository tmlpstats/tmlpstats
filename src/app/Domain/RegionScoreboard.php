<?php
namespace TmlpStats\Domain;

use App;
use Cache;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Encapsulations;

/**
 * Represents a region's scoreboard data
 *
 * This is one of the most common datasets used for reporting/UI.
 * This serves as a convenient way to cache the results so we don't have
 * to repeatedly generate it.
 */
class RegionScoreboard implements Arrayable, \JsonSerializable
{
    protected $context;
    protected $region;
    protected $reportingDate;
    protected $scoreboard;

    protected $centers = [];

    public function __construct(Models\Region $region, Carbon $reportingDate, Api\Context $context = null)
    {
        $this->region = $region;
        $this->reportingDate = $reportingDate;
        $this->context = $context ?: App::make(Api\Context::class);

        $this->scoreboard = $this->getQuarterScoreboardByCenter($reportingDate, $region);
    }

    public static function ensure(Models\Region $region, Carbon $reportingDate)
    {
        return App::make(Api\Context::class)->getEncapsulation(self::class, compact('region', 'reportingDate'));
    }

    public function getScoreboard()
    {
        return $this->scoreboard;
    }

    protected function getQuarterScoreboardByCenter(Carbon $reportingDate, Models\Region $region)
    {
        $rrd = Encapsulations\RegionReportingDate::ensure($region, $reportingDate);
        $rq = $rrd->getRegionQuarter();
        $quarter = $rrd->getQuarter();

        $globalReports = Models\GlobalReport::between($rq->firstWeekDate, $reportingDate)
            ->get()
            ->keyBy(function($gr) { return $gr->reportingDate->toDateString(); });

        $this->centers = Models\Center::byRegion($region)
            ->get()
            ->keyBy(function($c) { return $c->id; });

        // First, collect all of the official stats reports
        $statsReports = [];
        for ($targetDate = $rq->firstWeekDate; $targetDate->lte($reportingDate); $targetDate = $targetDate->copy()->addWeek()) {
            $gr = $globalReports->get($targetDate->toDateString());
            if (!$gr) {
                continue;
            }

            $srs = $gr->statsReports()
                      ->byRegion($region)
                      ->validated()
                      ->get()
                      ->keyBy(function($report) { return $report->id; });

            $statsReports = array_merge($statsReports, $srs->all());
        }
        $statsReports = collect($statsReports)->keyBy(function($report) { return $report->id; });

        // Then, get the scoreboard data and format to be consumed by ScoreboardMultiWeek
        // We have to sort this list by statsReport's reporting date in case a report is
        // resubmitted later in the quarter. Otherwise, the csd will be loaded in the wrong
        // order and we may get inaccurate original promises
        $csds = Models\CenterStatsData::whereIn('stats_report_id', $statsReports->keys())
            ->get()
            ->sortBy(function($csd) use ($statsReports) {
                return $statsReports->get($csd->statsReportId)->reportingDate->toDateString();
            });

        $csdByCenter = [];
        foreach ($csds as $csd) {
            $report = $statsReports->get($csd->statsReportId);
            $cid = $report->centerId;
            $center = $this->centers[$cid];

            $cq = $this->context->getEncapsulation(CenterQuarter::class, compact('center', 'quarter'));
            $milestone2 = $cq->getRepromiseDate();

            $type = $csd->type;
            if ($type === 'promise'
                && $reportingDate->gte($milestone2)
                && $report->reportingDate->lt($milestone2)
            ) {
                // The original promise is the "official" promise before new promises were submitted.
                // Sometimes promises are corrected on reports submitted after week 1. To capture these correctly,
                // we use the most recently submitted promise that was submitted before milestone 2.
                $csdByCenter[$center->name][$csd->reportingDate->toDateString()]['original'] = $csd;
            }

            $csdByCenter[$center->name][$csd->reportingDate->toDateString()][$type] = $csd;
        }
        ksort($csdByCenter);

        // Finally, hydrate the scoreboard objects
        return collect($csdByCenter)
            ->map(function($centerData, $center) {
                return ScoreboardMultiWeek::fromArray($centerData);
            });
    }

    public function toArray()
    {
        $output = [];
        foreach ($this->scoreboard as $center => $sb) {
            $output[$center] = $sb->toArray();
        }
        return $output;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
