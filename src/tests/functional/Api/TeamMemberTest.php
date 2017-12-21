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

class TeamMemberTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();
        config(['tmlp.earliest_submission' => '2015-01-01']);

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();
        $this->lastQuarter = Models\Quarter::year(2015)->quarterNumber(4)->first();
        $this->nextQuarter = Models\Quarter::year(2016)->quarterNumber(2)->first();

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at' => '2017-01-01',
            'version' => 'test',
        ]);

        $this->globalReport = Models\GlobalReport::firstOrCreate(['reporting_date' => $this->report->reportingDate]);
        $this->globalReport->addCenterReport($this->report);

        $this->teamMember = factory(Models\TeamMember::class)->create([
            'incoming_quarter_id' => $this->lastQuarter->id,
        ]);

        $this->teamMemberData = Models\TeamMemberData::firstOrCreate([
            'team_member_id' => $this->teamMember->id,
            'stats_report_id' => $this->report->id,
        ]);

        $this->headers = ['Accept' => 'application/json'];
    }

    /**
     * @dataProvider providerStash
     */
    public function testStash($data)
    {
        $user = $this->createUser('localStatistician', true);
        $context = MockContext::defaults()->withUser($user)->install();
        $tmApi = App::make(Api\TeamMember::class);

        $defaults = ['atWeekend' => true, 'gitw' => true, 'tdo' => 1, 'teamYear' => 1];
        $input = array_merge($defaults, $data['input']);
        $v = $input['incomingQuarter'];
        $input['incomingQuarter'] = $this->$v->id;

        $result = $tmApi->stash($this->center, $this->report->reportingDate, $input);
        $this->assertEquals($data['success'], $result['success']);
        if ($data['success']) {
            $this->assertTrue($result['storedId'] < 0);
        }
        $this->assertEquals($data['numMessages'], count($result['messages']));
    }

    public function providerStash()
    {
        return [
            [[
                'input' => ['firstName' => 'person ', 'lastName' => 'One', 'incomingQuarter' => 'lastQuarter'],
                'success' => true,
                'numMessages' => 0,
            ]],

            [[
                'input' => ['firstName' => 'person', 'lastName' => 'Two', 'incomingQuarter' => 'lastQuarter', 'gitw' => null],
                'success' => false,
                'numMessages' => 1,
            ]],
        ];
    }

    public function testAllForCenter($reportingDate = null)
    {

        $user = $this->createUser('localStatistician', true);
        $context = MockContext::defaults()->withUser($user)->install();
        $tmApi = App::make(Api\TeamMember::class);
        $results = collect($tmApi->allForCenter($this->center, $this->report->reportingDate, true));
        $this->assertEquals(1, $results->count());
        $tm = $results->get($this->teamMember->id);
        $this->assertEquals($this->teamMember->id, $tm->id);
        $this->assertFalse(array_get($tm->meta, 'canDelete', false));
    }

    /**
     * @dataProvider providerApiThrowsExceptionForInvalidDate
     */
    public function testApiThrowsExceptionForInvalidDate($method)
    {
        $reportingDate = Carbon::parse('this thursday', $this->center->timezone)
            ->startOfDay()
            ->toDateString();

        $parameters = [
            'method' => $method,
            'reportingDate' => $reportingDate,
            'center' => $this->center->id,
            'data' => [],
        ];

        $expectedResponse = [
            'success' => false,
            'error' => [
                'message' => 'Reporting date must be a Friday.',
            ],
        ];

        $this->post('/api', $parameters, $this->headers)
             ->seeJsonHas($expectedResponse)
             ->seeStatusCode(400);
    }

    public function providerApiThrowsExceptionForInvalidDate()
    {
        return [
            ['TeamMember.allForCenter'],
            ['TeamMember.stash'],
        ];
    }

    /**
     * @dataProvider providerBulkStashWeeklyReporting
     */
    public function testBulkStashWeeklyReporting($data)
    {
        $user = $this->createUser('localStatistician', true);
        $context = MockContext::defaults()->withUser($user)->install();
        $tmApi = App::make(Api\TeamMember::class);

        // TODO come back and set up multiple team members to test cases with multiple updates
        $update1 = $data['input'];
        $update1['id'] = $this->teamMember->id;

        $tmApi->bulkStashWeeklyReporting($this->center, $this->report->reportingDate, [$update1]);

        // Check the stashes
        $submissionData = App::make(Api\SubmissionData::class);

        $domain = $submissionData->get($this->center, $this->report->reportingDate, Domain\TeamMember::class, $this->teamMember->id);
        $data['checks']($this, $domain);
    }

    public function providerBulkStashWeeklyReporting()
    {
        return [
            [[
                'input' => ['gitw' => true, 'tdo' => 1],
                'checks' => function ($suite, $domain) {
                    $suite->assertEquals(true, $domain->gitw);
                    $suite->assertEquals(1, $domain->tdo);
                },
            ]],
            [[
                'input' => ['travel' => true, 'room' => null],
                'checks' => function ($suite, $domain) {
                    $suite->assertEquals(true, $domain->travel);
                    $suite->assertEquals(null, $domain->room);
                },
            ]],
            [[
                'input' => ['rppCap' => '1', 'rppCpc' => 2, 'rppLf' => '0'],
                'checks' => function ($suite, $domain) {
                    $suite->assertEquals(1, $domain->rppCap);
                    $suite->assertEquals(2, $domain->rppCpc);
                    $suite->assertEquals(0, $domain->rppLf);

                },
            ]],
        ];
    }
}
