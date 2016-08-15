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

class ValidationDataObjectTest extends FunctionalTestAbstract
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

        $this->context = MockContext::defaults()->withCenter($this->center)->install();
        $this->api = App::make(Api\ValidationData::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testValidate_unauthorized()
    {
        $this->expectException(Api\Exceptions\UnauthorizedException::class);
        $this->api->validate($this->center, $this->now);
    }
}
