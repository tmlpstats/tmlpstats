<?php
namespace TmlpStats\Tests\Api;

use App;
use TmlpStats\Api;
use TmlpStats\Api\LocalReport;
use TmlpStats\Http\Controllers\CenterStatsController;
use TmlpStats\Reports\Arrangements;
use TmlpStats\StatsReport;
use TmlpStats\Tests\TestAbstract;

class LocalReportTest extends TestAbstract
{
    protected $instantiateApp = true;
    protected $testClass = Api\LocalReport::class;

    /**
     * This test is a bit ridiculous, but it's there to evaluate the concept of service containers
     * as a method for injection and seeing what we can do with them.
     *
     * TODO abstract some of these facilities into a nicer paradigm
     */
    public function testGetQuarterScoreboard()
    {
        $this->markTestIncomplete('Test needs to be rewritten after we have db testing support.');

        $statsReport = new StatsReport();
        $centerStatsData = ['evil'];
        $weeklyPromises = ['reportData' => 'foo'];

        $controller = $this->getMockBuilder(CenterStatsController::class)
            ->setMethods(['getByStatsReport'])
            ->getMock();
        $controller->expects($this->once())
            ->method('getByStatsReport')
            ->with($this->equalTo($statsReport))
            ->willReturn($centerStatsData);

        App::instance(CenterStatsController::class, $controller);

        $arrangement = $this->getMockBuilder(Arrangements\GamesByWeek::class)
            ->setMethods(['compose'])
            ->getMock();
        $arrangement->expects($this->once())->method('compose')
            ->with($this->equalTo($centerStatsData))
            ->willReturn($weeklyPromises);
        App::instance(Arrangements\GamesByWeek::class, $arrangement);

        $result = App::make(LocalReport::class)->getQuarterScoreboard($statsReport);
        $this->assertEquals('foo', $result);
    }
}
