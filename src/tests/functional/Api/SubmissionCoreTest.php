<?php
namespace TmlpStats\Tests\Functional\Api;

use App;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;
use TmlpStats\Tests\Mocks\MockContext;

class SubmissionCoreTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    protected $centerId = 1;

    public function setUp()
    {
        parent::setUp();

        $reportingDateStr = '2016-04-15';
        $this->reportingDate = Carbon::parse($reportingDateStr);

        $this->center = Models\Center::find($this->centerId);
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();

        $this->context = MockContext::defaults()->withCenter($this->center)->install();

        $this->api = App::make(Api\SubmissionCore::class);
    }

    public function testCompleteSubmissionFailsAuth()
    {
        $this->expectException(Api\Exceptions\UnauthorizedException::class);

        $this->context->withOverrideCan(function($priv, $center) {
            return false;
        })->install();

        $this->api->completeSubmission($this->center, $this->reportingDate);
    }

    /**
     * @dataProvider providerCompleteSubmissions
     */
    public function testCompleteSubmissionSucceeds($validationResults, $expectedResults)
    {
        $this->context->withOverrideCan(function($priv, $center) {
            return ($priv === 'submitStats' && $center->id == $this->center->id);
        })->install();

        $validateApi = $this->getMockBuilder(Api\ValidationData::class)
                            ->setMethods(['validate'])
                            ->disableOriginalConstructor()
                            ->getMock();
        $validateApi->expects($this->once())
                    ->method('validate')
                    ->with($this->equalTo($this->center), $this->equalTo($this->reportingDate))
                    ->willReturn($validationResults);

        App::bind(Api\ValidationData::class, function ($app) use ($validateApi) {
            return $validateApi;
        });

        $result = $this->api->completeSubmission($this->center, $this->reportingDate);

        $this->assertEquals($expectedResults, $result);
    }

    public function providerCompleteSubmissions()
    {
        return [
            // Validation succeeds and submission returns success
            [
                [
                    'success' => true,
                    'valid' => true,
                    'messages' => [],
                ],
                [
                    'success' => true,
                    'id' => $this->centerId,
                ],
            ],
            // Validation fails and submission returns failure
            [
                [
                    'success' => true,
                    'valid' => false,
                    'messages' => ['messages'],
                ],
                [
                    'success' => false,
                    'id' => $this->centerId,
                    'message' => 'Validation failed'
                ],
            ],
        ];
    }
}
