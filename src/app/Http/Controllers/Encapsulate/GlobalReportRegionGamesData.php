<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;
use TmlpStats\Encapsulations;

class GlobalReportRegionGamesData
{
    private $globalReport;
    private $region;
    private $data = null;

    public function __construct(Models\GlobalReport $globalReport, Models\Region $region)
    {
        $this->globalReport = $globalReport;
        $this->region = $region;
        $this->data = $this->getGamesData($globalReport->reportingDate, $region);
    }

    // TODO: this logic should really be moved into the LocalReport and GlobalReport scoreboard getter methods
    protected function getGamesData(Carbon $reportingDate, Models\Region $region)
    {
        $rrd = Encapsulations\RegionReportingDate::ensure($region, $reportingDate);
        $rq = $rrd->getRegionQuarter();

        $globalReports = Models\GlobalReport::between($rq->firstWeekDate, $reportingDate)
            ->get()
            ->keyBy(function($gr) { return $gr->reportingDate->toDateString(); });

        $centers = Models\Center::all()->keyBy(function($center) { return $center->id; });

        $csdByCenter = [];

        // First, collect all of the centerStats data objects
        for ($targetDate = $rq->firstWeekDate; $targetDate->lte($reportingDate); $targetDate = $targetDate->copy()->addWeek()) {
            $gr = $globalReports->get($targetDate->toDateString());

            $statsReports = $gr->statsReports()
                               ->byRegion($region)
                               ->validated()
                               ->get()
                               ->keyBy(function($report) { return $report->id; });
            $csds = Models\CenterStatsData::whereIn('stats_report_id', $statsReports->keys())
                                          ->between($rq->firstWeekDate, $reportingDate)
                                          ->get();
            foreach ($csds as $csd) {
                $centerId = $statsReports->get($csd->statsReportId)->centerId;
                $centerName = $centers[$centerId]->name;
                $csdByCenter[$centerName][] = $csd;
            }
        }
        ksort($csdByCenter);

        // Then, format and filter data
        $output = [];
        foreach($csdByCenter as $center => $items) {
            // filter out duplicates from csdByCenter, keeping the latest promise/actual for each
            // keyBy will end up keeping the last reported thing with the same key... we use that as an easy dedup: https://laravel.com/docs/5.2/collections#method-keyby
            $csdByCenter[$center] = collect($items)
                ->keyBy(function($csd) { return "{$csd->type} " . $csd->reportingDate->toDateString(); })
                ->sortBy(function($csd) { return $csd->reportingDate->toDateString(); }); // re-sort by reportingDate in case later promises took over older ones

            // re-key to be consumed by ScoreboardMultiWeek
            $centerData = [];
            foreach ($items as $csd) {
                $centerData[$csd->reportingDate->toDateString()][$csd->type] = $csd;
            }

            // hydrate ScoreboardMultiWeek and filter out unneeded data
            $centerScoreboards = Domain\ScoreboardMultiWeek::fromArray($centerData)->toArray();
            foreach ($centerScoreboards as $date => $sb) {
                foreach (Domain\Scoreboard::GAME_KEYS as $game) {
                    $output[$game][$center][$date] = [
                        'promise' => $sb['games'][$game]['promise'],
                        'actual' => $sb['games'][$game]['actual'],
                        'effective' => ($sb['games'][$game]['actual'] >= $sb['games'][$game]['promise']),
                    ];
                }
            }
        }

        return $output;
    }

    public function getOne($page)
    {
        $globalReport = $this->globalReport;
        $region = $this->region;
        $data = $this->data;

        switch (strtolower($page)) {
            case 'accesstopowereffectiveness':
                $game = 'cap';
                break;
            case 'powertocreateeffectiveness':
                $game = 'cpc';
                break;
            case 'team1expansioneffectiveness':
                $game = 't1x';
                break;
            case 'team2expansioneffectiveness':
                $game = 't2x';
                break;
            case 'gameintheworldeffectiveness':
                $game = 'gitw';
                break;
            case 'landmarkforumeffectiveness':
                $game = 'lf';
                break;
            default:
                throw new \Exception("Unknown page {$page}");
        }

        $regionQuarter = App::make(Api\Context::class)->getEncapsulation(Encapsulations\RegionQuarter::class, [
            'quarter' => Models\Quarter::getQuarterByDate($globalReport->reportingDate, $region),
            'region' => $region,
        ]);

        return [
            'game' => $game,
            'reportData' => $data[$game],
            'milestones' => $regionQuarter->toArray(),
        ];
    }
}
