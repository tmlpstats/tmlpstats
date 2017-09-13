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
        $this->data = App::make(Api\GlobalReport::class)->getQuarterScoreboardByCenter($globalReport->reportingDate, $region);
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
            'reportData' => $data,
            'milestones' => $regionQuarter->toArray(),
        ];
    }
}
