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

class ScoreboardTest extends FunctionalTestAbstract
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

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at' => null,
            'version' => 'test',
        ]);

        $this->now = Carbon::parse('2016-04-15 18:45:00');
        Carbon::setTestNow($this->now);
    }

    /**
     * @dataProvider providerStash
     */
    public function testStash($reportingDate)
    {
        $reportingDateString = $this->report->reportingDate->toDateString();
        $parameters = [
            'method' => 'Scoreboard.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'week' => $reportingDateString,
                'promise' => [
                    'cap' => 0,
                    'cpc' => 1,
                    't1x' => 2,
                    't2x' => 3,
                    'gitw' => 4,
                    'lf' => 5,
                ],
                'actual' => [
                    'cap' => 0,
                    'cpc' => 0,
                    't1x' => 1,
                    't2x' => 2,
                    'gitw' => 3,
                    'lf' => 4,
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'valid' => false, // false because we don't bother to create the necessary objects to make scores accurate
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);

        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\Scoreboard::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals(0, $result->game('cap')->promise());
        $this->assertEquals(1, $result->game('cpc')->promise());
        $this->assertEquals(2, $result->game('t1x')->promise());
        $this->assertEquals(3, $result->game('t2x')->promise());
        $this->assertEquals(4, $result->game('gitw')->promise());
        $this->assertEquals(5, $result->game('lf')->promise());
        $this->assertEquals(0, $result->game('cap')->actual());
        $this->assertEquals(0, $result->game('cpc')->actual());
        $this->assertEquals(1, $result->game('t1x')->actual());
        $this->assertEquals(2, $result->game('t2x')->actual());
        $this->assertEquals(3, $result->game('gitw')->actual());
        $this->assertEquals(4, $result->game('lf')->actual());
    }

    public function providerStash()
    {
        return [
            ['2016-04-22'], // Non-existent report
            ['2016-04-15'], // Existing report
        ];
    }

    public function testStashFailsValidation()
    {
        $reportingDate = '2016-04-15';

        $reportingDateString = $this->report->reportingDate->toDateString();
        $parameters = [
            'method' => 'Scoreboard.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'week' => $reportingDateString,
                'promise' => [
                    'cap' => 0,
                    'cpc' => 1,
                    't1x' => 2,
                    't2x' => 3,
                    'gitw' => 4,
                    'lf' => 5,
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'valid' => false,
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);

        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\Scoreboard::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals(0, $result->game('cap')->promise());
        $this->assertEquals(1, $result->game('cpc')->promise());
        $this->assertEquals(2, $result->game('t1x')->promise());
        $this->assertEquals(3, $result->game('t2x')->promise());
        $this->assertEquals(4, $result->game('gitw')->promise());
        $this->assertEquals(5, $result->game('lf')->promise());
        $this->assertEquals(null, $result->game('cap')->actual());
        $this->assertEquals(null, $result->game('cpc')->actual());
        $this->assertEquals(null, $result->game('t1x')->actual());
        $this->assertEquals(null, $result->game('t2x')->actual());
        $this->assertEquals(null, $result->game('gitw')->actual());
        $this->assertEquals(null, $result->game('lf')->actual());
    }

    /**
     * @dataProvider providerApiThrowsExceptionForInvalidDate
     */
    public function testApiThrowsExceptionForInvalidDate($method, $data = null)
    {
        $reportingDate = Carbon::parse('this thursday', $this->center->timezone)
            ->startOfDay()
            ->toDateString();

        $parameters = [
            'method' => $method,
            'reportingDate' => $reportingDate,
            'center' => $this->center->id,
        ];

        if ($data !== null) {
            $parameters['data'] = $data;
        }

        $expectedResponse = [
            'success' => false,
            'error' => [
                'message' => 'Reporting date must be a Friday.',
            ],
        ];

        $headers = ['Accept' => 'application/json'];
        $this->post('/api', $parameters, $headers)->seeJsonHas($expectedResponse);
    }

    public function providerApiThrowsExceptionForInvalidDate()
    {
        return [
            ['Scoreboard.allForCenter'],
            ['Scoreboard.stash', []],
        ];
    }
}
