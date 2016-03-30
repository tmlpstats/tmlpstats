<?php
namespace TmlpStats\Tests\Api;

use App;
use Carbon\Carbon;
use Mockery;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Api;
use TmlpStats\Reports\Arrangements;
use TmlpStats\Tests\TestAbstract;

class LiveScoreboardTest extends TestAbstract
{
    protected $instantiateApp = true;
    protected $testClass = Api\LiveScoreboard::class;

    public function testGetOfficialScores()
    {
        $expectedResult = [];
        $quarterData = [];


        $center = new \stdClass;
        $reportDate = Carbon::create(2016, 3, 25);

        $quarter = Mockery::mock(Models\Quarter::class)getQuarterEndDate
        $quarter->shouldReceive('getQuarterEndDate')
                ->with($center)
                ->andReturn($quarter);

        $report = Mockery::mock(Models\StatsReport::class);
        $report->shouldReceive('getAttribute')
                ->with('quarter')
                ->andReturn($quarter);
        $report->shouldReceive('getAttribute')
                ->with('center')
                ->andReturn($center);
        $report->shouldReceive('getAttribute')
                ->with('reportingDate')
                ->andReturn($reportingDate);

        // Set carbon now override

        $reportData = []


        $localReport = Mockery::mock();

        $localReport->shouldReceive('getQuarterScoreboard')
                    ->once()
                    ->with($report, true)
                    ->andReturn($quarterData);

        App::instance(LocalReport::class, $localReport);

        $class = App::make(Api\LiveScoreboard::class);
        $this->assertEquals('yey!', $class->methodToTest());
    }

    public function testSetScore()
    {
        $game = 'cap';
        $type = 'actual';
        $value = 1234;

        $center     = new Models\Center();
        $center->id = 1;

        $currentScoresArray = ['games' => ['so', 'many']];
        $expectedResult = $currentScoresArray;
        $expectedResult['success'] = true;

        $currentScores = $this->getMockBuilder(Domain\Scoreboard::class)
                              ->setMethods(['toArray'])
                              ->disableOriginalConstructor()
                              ->getMock();

        $currentScores->expects($this->once())
                      ->method('toArray')
                      ->willReturn($currentScoresArray);

        $item = $this->getMockBuilder(Models\TempScoreboard::class)
                     ->setMethods(['save'])
                     ->getMock();

        $item->expects($this->once())
             ->method('save');

        $api = $this->getObjectMock([
            'logChanges',
            'getTempScoreboardGame',
            'getCurrentScores',
        ], [App::make(Api\Context::class)]);

        $api->expects($this->once())
            ->method('logChanges')
            ->with($this->equalTo([
                'center_id' => $center->id,
                'game' => $game,
                'type' => $type,
                'value' => $value,
            ]));

        $api->expects($this->once())
            ->method('getTempScoreboardGame')
            ->with($this->equalTo([
                'center_id' => $center->id,
                'routing_key' => "live.{$game}.{$type}",
            ]))
            ->willReturn($item);

        $api->expects($this->once())
            ->method('getCurrentScores')
            ->with($this->equalTo($center))
            ->willReturn($currentScores);

        $result = $api->setScore($center, $game, $type, $value);

        $this->assertEquals($expectedResult, $result);
    }

    public function testSetScoreThrowsExceptionWhenTypeNotActual()
    {
        $this->setExpectedException(\Exception::class);

        $game = 'cap';
        $type = 'promise';
        $value = 1234;

        $center     = new Models\Center();
        $center->id = 1;

        $api = $this->getObjectMock([
            'getCurrentScores',
        ], [App::make(Api\Context::class)]);

        $api->setScore($center, $game, $type, $value);
    }

    /**
     * @dataProvider providerGetCurrentScores
     */
    public function testGetCurrentScores($reportData, $tempScores, $expectedResult)
    {
        $center     = new Models\Center();
        $center->id = 1;

        $report = new Models\StatsReport();
        $report->setDateFormat('Y-m-d H:i:s');
        $report->reportingDate = Carbon::parse('2016-03-25')->startOfDay();

        $api = $this->getObjectMock([
            'getLatestReport',
            'getOfficialScores',
            'getTempScoreboardForCenter',
        ], [App::make(Api\Context::class)]);

        $api->expects($this->once())
            ->method('getLatestReport')
            ->with($this->equalTo($center))
            ->willReturn($report);

        $api->expects($this->once())
            ->method('getOfficialScores')
            ->with($this->equalTo($report))
            ->willReturn($reportData);

        $api->expects($this->once())
            ->method('getTempScoreboardForCenter')
            ->with($this->equalTo(1), $this->equalTo($report->reportingDate))
            ->willReturn($tempScores);

        $result = $api->getCurrentScores($center);

        $this->assertInstanceOf(Domain\Scoreboard::class, $result);

        foreach ($result->games() as $game) {
            $this->assertInstanceOf(Domain\ScoreboardGame::class, $game);

            $this->assertEquals($expectedResult[$game->key]['promise'], $game->promise());
            $this->assertEquals($expectedResult[$game->key]['actual'], $game->actual());
        }
    }

    public function providerGetCurrentScores()
    {
        $blankScoreboard = [
            'cap'  => [
                'promise' => 0,
                'actual'  => null,
            ],
            'cpc'  => [
                'promise' => 0,
                'actual'  => null,
            ],
            't1x'  => [
                'promise' => 0,
                'actual'  => null,
            ],
            't2x'  => [
                'promise' => 0,
                'actual'  => null,
            ],
            'gitw' => [
                'promise' => 0,
                'actual'  => null,
            ],
            'lf'   => [
                'promise' => 0,
                'actual'  => null,
            ],
        ];

        $seedData = [
            'cap'  => [
                'promise' => 10,
                'actual'  => 1,
            ],
            'cpc'  => [
                'promise' => 20,
                'actual'  => 2,
            ],
            't1x'  => [
                'promise' => 30,
                'actual'  => 3,
            ],
            't2x'  => [
                'promise' => 40,
                'actual'  => 4,
            ],
            'gitw' => [
                'promise' => 50,
                'actual'  => 5,
            ],
            'lf'   => [
                'promise' => 60,
                'actual'  => 6,
            ],
        ];

        $tempData = [
            'cap'  => [
                'actual'  => 2,
            ],
            'cpc'  => [
                'actual'  => 4,
            ],
            't1x'  => [
                'actual'  => 6,
            ],
            't2x'  => [
                'actual'  => 8,
            ],
            'gitw' => [
                'actual'  => 10,
            ],
            'lf'   => [
                'actual'  => 12,
            ],
        ];

        $data = [];

        // Blank scoreboard and no temp table overrides
        $data[] = [
            [],
            [],
            $blankScoreboard,
        ];

        // Populated scoreboard and no temp table overrides
        $data[] = [
            $this->generateScoreboard($seedData),
            [],
            $seedData,
        ];

        // Populated scoreboard with temp table overrides
        $data[] = [
            $this->generateScoreboard($seedData),
            $this->generateTempScoreboard($tempData),
            $this->generateMergedScoreboard($seedData, $tempData),
        ];

        // Verify updated promises are NOT returned
        $tempDataWithPromises = $tempData;
        foreach ($seedData as $game => $gameData) {
            $tempDataWithPromises[$game]['promise'] =  $gameData['promise'] + 1;
        }

        $data[] = [
            $this->generateScoreboard($seedData),
            $this->generateTempScoreboard($tempDataWithPromises),
            $this->generateMergedScoreboard($seedData, $tempData),
        ];

        return $data;
    }

    /**
     * Generate array of data for mocking getOfficialScores() output
     *
     * @param array $data
     *     In format:
     *         [ 'cap' => [ 'promise' => 123, 'actual => 120 ], 'cpc' => [...], ... ]
     *
     * @return array
     */
    protected function generateScoreboard($data)
    {
        $result = [];

        foreach ($data as $game => $gameData) {
            if (isset($gameData['promise'])) {
                $result["promise.{$game}"] = $gameData['promise'];
            }
            if (isset($gameData['actual'])) {
                $result["actual.{$game}"] = $gameData['actual'];
            }
        }

        return $result;
    }

    /**
     * Generate array of TmlpStats\TempScoreboard objects
     *
     * @param array $data
     *     In format:
     *         [ 'cap' => [ 'actual => 123 ], 'cpc' => [...], ... ]
     *
     * @return array
     */
    protected function generateTempScoreboard($data)
    {
        $result = [];

        foreach ($data as $game => $gameData) {
            if (isset($gameData['actual'])) {
                $tempScoreboard = new Models\TempScoreboard();
                $tempScoreboard->value = $gameData['actual'];

                $key = "live.{$game}.actual";
                $result[$key] = $tempScoreboard;
            }

            if (isset($gameData['promise'])) {
                $tempScoreboard = new Models\TempScoreboard();
                $tempScoreboard->value = $gameData['promise'];

                $key = "live.{$game}.promise";
                $result[$key] = $tempScoreboard;
            }
        }

        return $result;
    }

    /**
     * Overrite fields in $seedData with values from $updatedData
     *
     * @param $seedData     Initial data
     * @param $updatedData  Data to merge into seedData
     *
     * @return array        Merged data
     */
    protected function generateMergedScoreboard($seedData, $updatedData)
    {
        $result = [];

        foreach ($updatedData as $game => $gameData) {
            $seedPromise = isset($seedData[$game]['promise'])
                ? $seedData[$game]['promise']
                : 0;

            $seedActual = isset($seedData[$game]['actual'])
                ? $seedData[$game]['actual']
                : null;

            $result[$game] = [
                'promise' => isset($gameData['promise'])
                    ? $gameData['promise']
                    : $seedPromise,
                'actual' => isset($gameData['actual'])
                    ? $gameData['actual']
                    : $seedActual,
            ];
        }

        return $result;
    }
}
