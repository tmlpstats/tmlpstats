<?php
namespace TmlpStats\Tests\Functional\Domain;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Domain;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;
use TmlpStats\Tests\Mocks\MockContext;

class CenterQuarterTest extends FunctionalTestAbstract
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
        $this->lastQuarter = Models\Quarter::year(2015)->quarterNumber(4)->first();
        $this->nextQuarter = Models\Quarter::year(2016)->quarterNumber(2)->first();

        $this->context = MockContext::defaults()->withCenter($this->center)->install();

    }

    public function testIdentity()
    {
        $cq1 = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $cq2 = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $this->assertSame($cq1, $cq2); // Assert that two calls to the encapsulation service give the same object.

        // Not same with a different quarter object
        $cqNext = Domain\CenterQuarter::ensure($this->center, $this->nextQuarter);
        $this->assertNotSame($cq1, $cqNext);

        // Do some quarter manipulation for the fun of it
        $cqNext2 = Domain\CenterQuarter::ensure($this->center, $cq1->quarter->getNextQuarter());
        $this->assertSame($cqNext, $cqNext2);

        $this->context->clearEncapsulations();
        $cqAfterClear = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $this->assertSame($cq1->quarter, $cqAfterClear->quarter); // The quarters are still the same object
        $this->assertNotSame($cq1, $cqAfterClear); // but the CQ's are not, since we cleared the encapsulations
    }

    public function testValues()
    {
        $cq = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $this->assertEquals(Carbon::parse('2016-02-19'), $cq->startWeekendDate);
        $this->assertEquals(Carbon::parse('2016-03-18'), $cq->classroom1Date);
        $this->assertEquals(Carbon::parse('2016-04-15'), $cq->classroom2Date);
        $this->assertEquals(Carbon::parse('2016-05-13'), $cq->classroom3Date);
        $this->assertEquals(Carbon::parse('2016-06-10'), $cq->endWeekendDate);
    }

    public function testRegionQuarterOverride()
    {
        Models\Setting::upsert([
            'name' => 'regionQuarterOverride',
            'center' => $this->center,
            'quarter' => $this->quarter,
            'value' => [
                'classroom3Date' => '2016-05-20',
                'endWeekendDate' => '2016-06-17',
            ],
        ]);

        $cq = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $this->assertEquals(Carbon::parse('2016-02-19'), $cq->startWeekendDate);
        $this->assertEquals(Carbon::parse('2016-03-18'), $cq->classroom1Date);
        $this->assertEquals(Carbon::parse('2016-04-15'), $cq->classroom2Date);
        $this->assertEquals(Carbon::parse('2016-05-20'), $cq->classroom3Date);
        $this->assertEquals(Carbon::parse('2016-06-17'), $cq->endWeekendDate);

    }

    public function test_getTravelDueByDate()
    {
        $cq = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $this->assertEquals($cq->classroom2Date, $cq->getTravelDueByDate());

        // Now try with CR#3 date override
        $this->context->clearEncapsulations();
        Models\Setting::upsert([
            'name' => 'travelDueByDate',
            'center' => $this->center,
            'quarter' => $this->quarter,
            'value' => 'classroom3Date',
        ]);

        $cq = Domain\CenterQuarter::ensure($this->center, $this->quarter);
        $this->assertEquals($cq->classroom3Date, $cq->getTravelDueByDate());

    }
}
