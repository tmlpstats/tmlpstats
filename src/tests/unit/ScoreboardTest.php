<?php
namespace TmlpStats\Tests;

use stdClass;
use TmlpStats\Scoreboard;
use TmlpStats\Util;

class ScoreboardTest extends TestAbstract
{
    protected $testClass = Scoreboard::class;

    /**
     * @dataProvider providerCalculatePoints
     */
    public function testCalculatePoints($promises, $actuals, $expectedResult)
    {
        $points = Scoreboard::calculatePoints($promises, $actuals);

        $this->assertEquals($expectedResult, $points);
    }

    public function providerCalculatePoints()
    {
        return [
            // 0% => 0 points
            [
                Util::arrayToObject([
                    'cap'  => '100',
                    'cpc'  => '100',
                    't1x'  => '100',
                    't2x'  => '100',
                    'gitw' => '100',
                    'lf'   => '100',
                ]),
                Util::arrayToObject([
                    'cap'  => '0',
                    'cpc'  => '0',
                    't1x'  => '0',
                    't2x'  => '0',
                    'gitw' => '0',
                    'lf'   => '0',
                ]),
                0,
            ],
            // 100% => 28 points
            [
                Util::arrayToObject([
                    'cap'  => '100',
                    'cpc'  => '100',
                    't1x'  => '100',
                    't2x'  => '100',
                    'gitw' => '100',
                    'lf'   => '100',
                ]),
                Util::arrayToObject([
                    'cap'  => '100',
                    'cpc'  => '100',
                    't1x'  => '100',
                    't2x'  => '100',
                    'gitw' => '100',
                    'lf'   => '100',
                ]),
                28,
            ],
            // Calculates total based on all games
            [
                Util::arrayToObject([
                    'cap'  => '100',
                    'cpc'  => '100',
                    't1x'  => '100',
                    't2x'  => '100',
                    'gitw' => '100',
                    'lf'   => '100',
                ]),
                Util::arrayToObject([
                    'cap'  => '75', // 2 points
                    'cpc'  => '80', // 2 points
                    't1x'  => '90', // 3 points
                    't2x'  => '100',// 4 points
                    'gitw' => '75', // 1 points
                    'lf'   => '70', // 0 points
                ]),
                12,
            ],
        ];
    }

    /**
     * @dataProvider providerCalculatePointsThrowsExceptionForInvalidInputs
     */
    public function testCalculatePointsThrowsExceptionForInvalidInputs($promises, $actuals)
    {
        $this->setExpectedException('\Exception');
        Scoreboard::calculatePoints($promises, $actuals);
    }

    public function providerCalculatePointsThrowsExceptionForInvalidInputs()
    {
        return [
            [null, null],
            [null, new stdClass],
            [new stdClass, null],
        ];
    }

    /**
     * @dataProvider providerCalculatePercent
     */
    public function testCalculatePercent($promise, $actual, $expectedResult)
    {
        $percent = Scoreboard::calculatePercent($promise, $actual);

        $this->assertEquals($expectedResult, $percent);
    }

    public function providerCalculatePercent()
    {
        return [
            // 100%
            [
                100,
                100,
                100,
            ],
            // 0%
            [
                100,
                0,
                0,
            ],
            // simple percent
            [
                100,
                87,
                87,
            ],
            // over 100% returns over 100%
            [
                100,
                110,
                110,
            ],
            // less than 0% returns 0%
            [
                100,
                -10,
                0,
            ],
            // simple %
            [
                4,
                3,
                75,
            ],
            // returns float
            [
                3,
                2,
                (float) ((2/3) * 100),
            ],
            // returns float
            [
                3,
                1,
                (float) ((1/3) * 100),
            ],
            // returns float
            [
                8,
                1,
                12.500,
            ],
            // Promise 0 always 0
            [
                0,
                5,
                0,
            ],
            // Promise < 0 always 0
            [
                -5,
                5,
                0,
            ],
        ];
    }

    /**
     * @dataProvider providerGetPoints
     */
    public function testGetPoints($percent, $game, $expectedResult)
    {
        $points = Scoreboard::getPoints($percent, $game);

        $this->assertEquals($expectedResult, $points);
    }

    public function providerGetPoints()
    {
        return [
            // CPC
            [
                100,
                'cpc',
                4,
            ],
            [
                99,
                'cpc',
                3,
            ],
            [
                90,
                'cpc',
                3,
            ],
            [
                89,
                'cpc',
                2,
            ],
            [
                80,
                'cpc',
                2,
            ],
            [
                79,
                'cpc',
                1,
            ],
            [
                75,
                'cpc',
                1,
            ],
            [
                74,
                'cpc',
                0,
            ],
            [
                0,
                'cpc',
                0,
            ],
            // T1x
            [
                100,
                't1x',
                4,
            ],
            [
                99,
                't1x',
                3,
            ],
            [
                90,
                't1x',
                3,
            ],
            [
                89,
                't1x',
                2,
            ],
            [
                80,
                't1x',
                2,
            ],
            [
                79,
                't1x',
                1,
            ],
            [
                75,
                't1x',
                1,
            ],
            [
                74,
                't1x',
                0,
            ],
            [
                0,
                't1x',
                0,
            ],
            // T2x
            [
                100,
                't2x',
                4,
            ],
            [
                99,
                't2x',
                3,
            ],
            [
                90,
                't2x',
                3,
            ],
            [
                89,
                't2x',
                2,
            ],
            [
                80,
                't2x',
                2,
            ],
            [
                79,
                't2x',
                1,
            ],
            [
                75,
                't2x',
                1,
            ],
            [
                74,
                't2x',
                0,
            ],
            [
                0,
                't2x',
                0,
            ],
            // GITW
            [
                100,
                'gitw',
                4,
            ],
            [
                99,
                'gitw',
                3,
            ],
            [
                90,
                'gitw',
                3,
            ],
            [
                89,
                'gitw',
                2,
            ],
            [
                80,
                'gitw',
                2,
            ],
            [
                79,
                'gitw',
                1,
            ],
            [
                75,
                'gitw',
                1,
            ],
            [
                74,
                'gitw',
                0,
            ],
            [
                0,
                'gitw',
                0,
            ],
            // LF
            [
                100,
                'lf',
                4,
            ],
            [
                99,
                'lf',
                3,
            ],
            [
                90,
                'lf',
                3,
            ],
            [
                89,
                'lf',
                2,
            ],
            [
                80,
                'lf',
                2,
            ],
            [
                79,
                'lf',
                1,
            ],
            [
                75,
                'lf',
                1,
            ],
            [
                74,
                'lf',
                0,
            ],
            [
                0,
                'lf',
                0,
            ],
            // CAP (applies multiplier)
            [
                100,
                'cap',
                8,
            ],
            [
                99,
                'cap',
                6,
            ],
            [
                90,
                'cap',
                6,
            ],
            [
                89,
                'cap',
                4,
            ],
            [
                80,
                'cap',
                4,
            ],
            [
                79,
                'cap',
                2,
            ],
            [
                75,
                'cap',
                2,
            ],
            [
                74,
                'cap',
                0,
            ],
            [
                0,
                'cap',
                0,
            ],
            // Capitalized game returns correct result
            [
                75,
                'CAP',
                2,
            ],
            // Float percent rounds down correctly (74.33333 => 74)
            [
                (float) ((1/3) + 74),
                'cap',
                0,
            ],
            // Float percent rounds up correctly (79.66666 => 80)
            [
                (float) ((2/3) + 79),
                'cap',
                4,
            ],
            // Float percent rounds up correctly (89.5000 => 90)
            [
                (float) ((1/2) + 89),
                'cap',
                6,
            ],
            // Over 100% returns max points
            [
                110,
                'cap',
                8,
            ],
        ];
    }

    public function testGetPointsThrowsExceptionForUnknownGame()
    {
        $this->setExpectedException('\Exception');

        Scoreboard::getPoints(100, 'asdf');
    }

    /**
     * @dataProvider providerGetRating
     */
    public function testGetRating($points, $expectedResult)
    {
        $rating = Scoreboard::getRating($points);

        $this->assertEquals($expectedResult, $rating);
    }

    public function providerGetRating()
    {
        return [
            [
                28,
                'Powerful'
            ],
            [
                27,
                'High Performing'
            ],
            [
                26,
                'High Performing'
            ],
            [
                25,
                'High Performing'
            ],
            [
                24,
                'High Performing'
            ],
            [
                23,
                'High Performing'
            ],
            [
                22,
                'High Performing'
            ],
            [
                21,
                'Effective'
            ],
            [
                20,
                'Effective'
            ],
            [
                19,
                'Effective'
            ],
            [
                18,
                'Effective'
            ],
            [
                17,
                'Effective'
            ],
            [
                16,
                'Effective'
            ],
            [
                15,
                'Marginally Effective'
            ],
            [
                14,
                'Marginally Effective'
            ],
            [
                13,
                'Marginally Effective'
            ],
            [
                12,
                'Marginally Effective'
            ],
            [
                11,
                'Marginally Effective'
            ],
            [
                10,
                'Marginally Effective'
            ],
            [
                9,
                'Marginally Effective'
            ],
            [
                8,
                'Ineffective'
            ],
            [
                7,
                'Ineffective'
            ],
            [
                6,
                'Ineffective'
            ],
            [
                5,
                'Ineffective'
            ],
            [
                4,
                'Ineffective'
            ],
            [
                3,
                'Ineffective'
            ],
            [
                2,
                'Ineffective'
            ],
            [
                1,
                'Ineffective'
            ],
            [
                0,
                'Ineffective'
            ],
        ];
    }

    /**
     * @dataProvider providerGetRatingThrowsExceptionForPointsOutOfRange
     */
    public function testGetRatingThrowsExceptionForPointsOutOfRange($points)
    {
        $this->setExpectedException('\Exception');

        Scoreboard::getRating($points);
    }

    public function providerGetRatingThrowsExceptionForPointsOutOfRange()
    {
        return [
            [Scoreboard::MAX_POINTS + 1],
            [Scoreboard::MIN_POINTS - 1],
        ];
    }
}
