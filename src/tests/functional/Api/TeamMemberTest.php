<?php
namespace TmlpStats\Tests\Functional\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

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

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();
        $this->lastQuarter = Models\Quarter::year(2015)->quarterNumber(4)->first();
        $this->nextQuarter = Models\Quarter::year(2016)->quarterNumber(2)->first();

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at' => null,
            'version' => 'test',
        ]);

        $this->teamMember = factory(Models\TeamMember::class)->create([
            'incoming_quarter_id' => $this->lastQuarter->id,
        ]);

        $this->teamMemberData = Models\TeamMemberData::firstOrCreate([
            'team_member_id' => $this->teamMember->id,
            'stats_report_id' => $this->report->id,
        ]);

        $this->headers = ['Accept' => 'application/json'];
    }

    public function testStash()
    {
        $this->markTestIncomplete('We need to add tests');
    }

    public function testAllForCenter($reportingDate = null)
    {
        $this->markTestIncomplete('We need to add tests');
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
}
