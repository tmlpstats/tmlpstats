<?php
namespace TmlpStats\Http\Controllers\Encapsulate;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
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
        $this->data = App::make(Api\GlobalReport::class)->getGamesDataAllWeeks($globalReport, $region);
    }

    protected function filterGame($game, array $reports, Models\Region $region)
    {
        $weeksData = [];
        foreach ($reports as $date => $weekReports) {
            foreach ($weekReports as $centerName => $centerData) {
                // Note: we're transforming the output from $array[date][center] to $array[center][date]
                $weeksData[$centerName][$date] = [
                    'promise' => $centerData['promise'][$game],
                    'actual' => $centerData['actual'][$game],
                    'effective' => ($centerData['actual'][$game] >= $centerData['promise'][$game]),
                ];
            }
        }
        return $weeksData;
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

        return view('globalreports.details.centergameeffectiveness', [
            'game' => $game,
            'reportData' => $this->filterGame($game, $data, $region),
            'milestones' => $regionQuarter->datesAsArray(),
        ]);
    }
}
