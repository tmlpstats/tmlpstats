<?php
namespace TmlpStats\Tests\Functional\Api;

use App;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;
use TmlpStats\Tests\Mocks\MockContext;

class ScoreboardObjectTest extends FunctionalTestAbstract
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

        $this->context = MockContext::defaults()->withCenter($this->center)->install();
        $this->sbapi = App::make(Api\Submission\Scoreboard::class);
    }

    public function testSetScoreboardLockQuarter_unauthorized()
    {
        $this->expectException(Api\Exceptions\UnauthorizedException::class);
        $this->sbapi->setScoreboardLockQuarter($this->center, $this->quarter, []);
    }

    public function testSetScoreboardLockQuarter_works()
    {
        $this->context->withFakedAdmin()->install();
        // set the lock
        $this->sbapi->setScoreboardLockQuarter($this->center, $this->quarter, ['weeks' => [
            ['week' => '2016-03-04', 'editPromise' => false, 'editActual' => true],
            ['week' => '2016-03-11', 'editPromise' => true, 'editActual' => false],
        ]]);

        // Get the value back
        $value = $this->context->getSetting(Api\Submission\Scoreboard::LOCK_SETTING_KEY, $this->center, $this->quarter);
        $this->assertEquals('2016-03-04', $value['weeks'][0]['week']);
        $this->assertEquals(true, $value['weeks'][0]['editActual']);
    }

    public function testGetScoreboardLockQuarter_typical()
    {
        $this->context->withFakedAdmin()->install();
        $this->sbapi->setScoreboardLockQuarter($this->center, $this->quarter, ['weeks' => [
            ['week' => '2016-03-04', 'editPromise' => false, 'editActual' => true],
            ['week' => '2016-03-11', 'editPromise' => true, 'editActual' => false],
        ]]);

        // Get the value back and make sure we can do things with it
        $value = $this->sbapi->getScoreboardLockQuarter($this->center, $this->quarter);
        $this->assertInstanceOf(Domain\ScoreboardLockQuarter::class, $value);

        $week1 = $value->getWeek(Carbon::createFromFormat('Y-m-d', '2016-03-04'));
        $this->assertEquals(true, $week1->editActual);
        $this->assertEquals(false, $week1->editPromise);
    }
}
