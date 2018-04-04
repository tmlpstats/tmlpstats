<?php
namespace TmlpStats\Tests\Unit;

use stdClass;
use Symfony\Component\Yaml\Yaml;
use TmlpStats\Domain\Scoreboard;
use TmlpStats\Domain\ScoreboardGame;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Util;

class ScoreboardTest extends TestAbstract
{
    protected $testClass = Scoreboard::class;

    protected function yamlProvider()
    {
        // Get the data for this test from a YAML file we can share between the JS and PHP tests
        return Yaml::parse(file_get_contents(__DIR__ . '/../inputs/scoreboard.yml'));
    }

    /**
     * @dataProvider providerPercent
     */
    public function testPercent($promise, $actual, $expectedResult)
    {
        $game = new ScoreboardGame('cap');
        $game->setPromise($promise);
        $game->setActual($actual);

        $this->assertEquals($expectedResult, $game->percent());
    }

    public function providerPercent()
    {
        return $this->yamlProvider()['percent'];
    }

    /**
     * @dataProvider providerPoints
     */
    public function testPoints($promise, $actual, $gameKey, $expectedResult)
    {
        $game = new ScoreboardGame($gameKey);
        $game->setPromise($promise);
        $game->setActual($actual);

        $this->assertEquals($expectedResult, $game->points());
    }

    public function providerPoints()
    {
        return $this->yamlProvider()['points'];
    }

    /**
     * @dataProvider providerCalculatePercent
     */
    public function testCalculatePercent($promise, $actual, $expectedResult)
    {
        $percent = ScoreboardGame::calculatePercent($promise, $actual);

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
                (float) ((2 / 3) * 100),
            ],
            // returns float
            [
                3,
                1,
                (float) ((1 / 3) * 100),
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
        $points = ScoreboardGame::getPoints($game, $percent);

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
                (float) ((1 / 3) + 74),
                'cap',
                0,
            ],
            // Float percent rounds up correctly (79.66666 => 80)
            [
                (float) ((2 / 3) + 79),
                'cap',
                4,
            ],
            // Float percent rounds up correctly (89.5000 => 90)
            [
                (float) ((1 / 2) + 89),
                'cap',
                6,
            ],
            // Float percent rounds up correctly (99.5000 => 99)
            [
                (float) ((1 / 2) + 99),
                'cap',
                6,
            ],
            // Float percent rounds up correctly (99.7500 => 99)
            [
                (float) ((3 / 4) + 99),
                'cap',
                6,
            ],
            // Float percent rounds up correctly (100.250 => 100)
            [
                (float) ((1 / 4) + 100),
                'cap',
                8,
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

        ScoreboardGame::getPoints('asdf', 100);
    }

    /**
     * @dataProvider providerGetRating
     */
    public function testGetRating($points, $expectedResult)
    {
        $rating = ScoreboardGame::getRating($points);

        $this->assertEquals($expectedResult, $rating);
    }

    public function providerGetRating()
    {
        return $this->yamlProvider()['ratings'];
    }

    /**
     * @dataProvider providerGetRatingThrowsExceptionForPointsOutOfRange
     */
    public function testGetRatingThrowsExceptionForPointsOutOfRange($points)
    {
        $this->setExpectedException('\Exception');

        ScoreboardGame::getRating($points);
    }

    public function providerGetRatingThrowsExceptionForPointsOutOfRange()
    {
        return [
            [ScoreboardGame::MAX_POINTS + 1],
            [ScoreboardGame::MIN_POINTS - 1],
        ];
    }
}
