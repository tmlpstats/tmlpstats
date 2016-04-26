<?php
namespace TmlpStats\Tests\Functional\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class LocalReportTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();
        $this->quarter->setRegion($this->center->region);

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => null,
            'version'        => 'test',
        ]);
    }

    public function generateScoreboard($center, Carbon $reportingDate)
    {
        $scoreboard = [];
        $reports = [];

        $weekNumber = 1;
        $date = $this->quarter->getFirstWeekDate($center);
        while ($date->lte($this->quarter->endWeekendDate)) {
            if ($date->lte($reportingDate)) {

                $globalReport = Models\GlobalReport::firstOrCreate([
                    'reporting_date' => $date,
                ]);

                $report = Models\StatsReport::firstOrCreate([
                    'center_id'      => $center->id,
                    'quarter_id'     => $this->quarter->id,
                    'reporting_date' => $date->toDateString(),
                    'validated'      => true,
                    'submitted_at'   => $date->toDateTimeString(),
                    'version'        => 'test',
                ]);

                $globalReport->addCenterReport($report);

                $scoreboard[$date->toDateString()]['actual'] = Models\CenterStatsData::firstOrCreate([
                    'reporting_date' => $date->toDateString(),
                    'stats_report_id' => $report->id,
                    'type' => 'actual',
                    'cap' => $weekNumber * 2 - 1,
                    'cpc' => $weekNumber * 2 - 1,
                    't1x' => $weekNumber * 2 - 1,
                    't2x' => $weekNumber * 2 - 1,
                    'gitw' => 80 + $weekNumber - 1,
                    'lf' => $weekNumber * 2 - 1,
                    'tdo' => mt_rand(25, 100),
                ]);
            }

            $reports[] = $report;

            $scoreboard[$date->toDateString()]['promise'] = Models\CenterStatsData::firstOrCreate([
                'reporting_date'  => $date->toDateString(),
                'stats_report_id' => $reports[0]->id, // Always attached to week 1
                'type'            => 'promise',
                'cap'             => $weekNumber * 2,
                'cpc'             => $weekNumber * 2,
                't1x'             => $weekNumber * 2,
                't2x'             => $weekNumber * 2,
                'gitw'            => 80 + $weekNumber,
                'lf'              => $weekNumber * 2,
                'tdo'             => 100,
            ]);

            $weekNumber++;
            $date->addWeek();
        }

        return $scoreboard;
    }

    public function testGetCurrentScore()
    {
        $parameters = [
            'method' => 'LocalReport.getQuarterScoreboard',
            'localReport' => $this->report->id,
            //'options' => ['returnUnprocessed' => true]
        ];

        $scoreboard = $this->generateScoreboard($this->center, $this->report->reportingDate);

        $this->post('/api', $parameters)->dump();//seeJsonHas($expectedResponse);
    }
}
