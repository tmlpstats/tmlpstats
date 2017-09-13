<?php
namespace TmlpStats\Tests\Functional\Domain;

use Carbon\Carbon;
use Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;
use TmlpStats\Tests\Mocks\MockContext;

class RegionScoreboardTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    protected $data = [
        '2017-06-09' => [
            'actual' => [
                'cap' => '3',
                'cpc' => '-9',
                't1x' => '0',
                't2x' => '0',
                'gitw' => '97',
                'lf' => '2',
                'points' => 3,
            ],
            'promise' => [
                'cap' => '5',
                'cpc' => '1',
                't1x' => '1',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '3',
            ],
        ],
        '2017-06-16' => [
            'actual' => [
                'cap' => '8',
                'cpc' => '-15',
                't1x' => '0',
                't2x' => '0',
                'gitw' => '93',
                'lf' => '4',
                'points' => 3,
            ],
            'promise' => [
                'cap' => '13',
                'cpc' => '1',
                't1x' => '1',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '6',
            ],
        ],
        '2017-06-23' => [
            'actual' => [
                'cap' => '21',
                'cpc' => '-14',
                't1x' => '0',
                't2x' => '0',
                'gitw' => '97',
                'lf' => '16',
                'points' => 13,
            ],
            'promise' => [
                'cap' => '23',
                'cpc' => '1',
                't1x' => '1',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '15',
            ],
        ],
        '2017-06-30' => [
            'actual' => [
                'cap' => '30',
                'cpc' => '-14',
                't1x' => '4',
                't2x' => '0',
                'gitw' => '100',
                'lf' => '22',
                'points' => 16,
            ],
            'promise' => [
                'cap' => '30',
                'cpc' => '1',
                't1x' => '6',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '20',
            ],
        ],
        '2017-07-07' => [
            'actual' => [
                'cap' => '34',
                'cpc' => '-14',
                't1x' => '5',
                't2x' => '-1',
                'gitw' => '70',
                'lf' => '25',
                'points' => 10,
            ],
            'promise' => [
                'cap' => '36',
                'cpc' => '1',
                't1x' => '11',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '22',
            ],
        ],
        '2017-07-14' => [
            'actual' => [
                'cap' => '37',
                'cpc' => '-13',
                't1x' => '5',
                't2x' => '-1',
                'gitw' => '100',
                'lf' => '26',
                'points' => 18,
            ],
            'original' => [
                'cap' => '42',
                'cpc' => '1',
                't1x' => '12',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '25',
            ],
            'promise' => [
                'cap' => '39',
                'cpc' => '1',
                't1x' => '5',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '25',
            ],
        ],
        '2017-07-21' => [
            'actual' => [
                'cap' => '39',
                'cpc' => '-11',
                't1x' => '4',
                't2x' => '-1',
                'gitw' => '100',
                'lf' => '28',
                'points' => 14,
            ],
            'original' => [
                'cap' => '45',
                'cpc' => '1',
                't1x' => '12',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '28',
            ],
            'promise' => [
                'cap' => '48',
                'cpc' => '1',
                't1x' => '5',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '28',
            ],
        ],
        '2017-07-28' => [
            'actual' => [
                'cap' => '43',
                'cpc' => '-10',
                't1x' => '4',
                't2x' => '-1',
                'gitw' => '63',
                'lf' => '30',
                'points' => 11,
            ],
            'original' => [
                'cap' => '45',
                'cpc' => '1',
                't1x' => '12',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '31',
            ],
            'promise' => [
                'cap' => '48',
                'cpc' => '1',
                't1x' => '5',
                't2x' => '1',
                'gitw' => '90',
                'lf' => '31',
            ],
        ],
        '2017-08-04' => [
            'actual' => [
                'cap' => '50',
                'cpc' => '22',
                't1x' => '4',
                't2x' => '0',
                'gitw' => '100',
                'lf' => '39',
                'points' => 19,
            ],
            'original' => [
                'cap' => '48',
                'cpc' => '7',
                't1x' => '13',
                't2x' => '2',
                'gitw' => '100',
                'lf' => '40',
            ],
            'promise' => [
                'cap' => '48',
                'cpc' => '7',
                't1x' => '6',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '40',
            ],
        ],
        '2017-08-11' => [
            'actual' => [
                'cap' => '52',
                'cpc' => '22',
                't1x' => '6',
                't2x' => '1',
                'gitw' => '100',
                'lf' => '41',
                'points' => 17,
            ],
            'original' => [
                'cap' => '54',
                'cpc' => '8',
                't1x' => '13',
                't2x' => '3',
                'gitw' => '100',
                'lf' => '43',
            ],
            'promise' => [
                'cap' => '54',
                'cpc' => '8',
                't1x' => '9',
                't2x' => '2',
                'gitw' => '100',
                'lf' => '43',
            ],
        ],
        '2017-08-18' => [
            'actual' => [
                'cap' => '60',
                'cpc' => '24',
                't1x' => '8',
                't2x' => '2',
                'gitw' => '90',
                'lf' => '45',
                'points' => 24,
            ],
            'original' => [
                'cap' => '60',
                'cpc' => '9',
                't1x' => '13',
                't2x' => '3',
                'gitw' => '100',
                'lf' => '45',
            ],
            'promise' => [
                'cap' => '60',
                'cpc' => '9',
                't1x' => '11',
                't2x' => '2',
                'gitw' => '90',
                'lf' => '45',
            ],
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->region = Models\Region::abbreviation('NA')->first();
        $this->reportingDate = Carbon::parse('2017-08-18');

        $this->context = MockContext::defaults()->withCenter($this->center)->install();
    }

    public function testWithOriginalPromises()
    {
        $this->reportingDate = Carbon::parse('2017-06-30');

        $sb = Domain\RegionScoreboard::ensure($this->region, $this->reportingDate)->getScoreboard();
        $sb = $sb[$this->center->name];

        foreach ($this->data as $week => $weekData) {
            $weekDate = Carbon::parse($week);
            foreach ($weekData as $type => $typeData) {
                foreach (Domain\Scoreboard::GAME_KEYS as $game) {
                    // This report only shows original promises
                    if ($type == 'promise') {
                        continue;
                    }
                    if ($type == 'original') {
                        $type = 'promise';
                    }

                    $expected  = $typeData[$game];
                    $actual = $sb->getWeek($weekDate)->game($game)->$type();

                    // Actuals should only be returned for weeks that haven't occurred yet
                    if ($type == 'actual' && $weekDate->gt($this->reportingDate)) {
                        $expected = null;
                    }

                    $this->assertEquals($expected, $actual, "{$week} {$game} {$type} does not match");
                }
            }
        }
    }

    public function testWithNewPromises()
    {
        $sb = Domain\RegionScoreboard::ensure($this->region, $this->reportingDate)->getScoreboard();
        $sb = $sb[$this->center->name];

        foreach ($this->data as $week => $weekData) {
            foreach ($weekData as $type => $typeData) {
                foreach (Domain\Scoreboard::GAME_KEYS as $game) {
                    if ($type == 'original') {
                        $type = 'originalPromise';
                    }
                    $expected  = $typeData[$game];
                    $actual = $sb->getWeek(Carbon::parse($week))->game($game)->$type();
                    $this->assertEquals($expected, $actual, "{$week} {$game} {$type} does not match");
                }
            }
        }
    }
}
