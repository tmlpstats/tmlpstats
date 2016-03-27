<?php
namespace TmlpStats\Tests\Domain;

use TmlpStats\Domain\Scoreboard;
use TmlpStats\Tests\TestAbstract;

class ScoreboardTest extends TestAbstract
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasic()
    {
        $scoreboard = Scoreboard::blank();
        $scoreboard->eachGame(function (&$game) {
            $game->setPromise(100);
            $game->setActual(92);
        });
        $this->assertEquals(21, $scoreboard->points());
        $this->assertEquals('Effective', $scoreboard->rating());
        $scoreboard->setValue('cap', 'actual', 88);
        $this->assertEquals(19, $scoreboard->points());
        $this->assertTrue(true);
    }

    public function testFromArray()
    {
        $v = [
            'promise' => [
                "cap" => 46, "cpc" => 19, "t1x" => 8, "t2x" => 2, "gitw" => 85, "lf" => 56,
            ],
            'actual' => [
                "cap" => 48, "cpc" => 23, "t1x" => 8, "t2x" => 0, "gitw" => 75, "lf" => 53,
            ],
        ];
        $scoreboard = Scoreboard::fromArray($v);
        $this->assertEquals(8, $scoreboard->game('cap')->points());
        $this->assertEquals(104, $scoreboard->game('cap')->percent());

        $this->assertEquals(4, $scoreboard->game('cpc')->points());
        $this->assertEquals(121, $scoreboard->game('cpc')->percent());

        $this->assertEquals(3, $scoreboard->game('lf')->points());
        $this->assertEquals(95, $scoreboard->game('lf')->percent());

        $this->assertEquals('Effective', $scoreboard->rating());
        $this->assertEquals(21, $scoreboard->points());
    }
}
