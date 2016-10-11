<?php
namespace TmlpStats\Tests\Functional\Api;

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
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate($parameterUpdates, $expectedResponseUpdates)
    {
        $parameters = [
            'method' => 'TeamMember.create',
            'data' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'center' => $this->center->id,
                'teamYear' => 2,
                'incomingQuarter' => $this->lastQuarter->id,
            ],
        ];

        $lastPersonId = Models\Person::count();
        $lastTeamMemberId = Models\TeamMember::count();

        $expectedResponse = [
            'id' => $lastTeamMemberId + 1,
            'teamYear' => $parameters['data']['teamYear'],
            'personId' => $lastPersonId + 1,
            'isReviewer' => false,
            'incomingQuarterId' => $parameters['data']['incomingQuarter'],
            'person' => [
                'id' => $lastPersonId + 1,
                'firstName' => $parameters['data']['firstName'],
                'lastName' => $parameters['data']['lastName'],
                'phone' => null,
                'email' => null,
                'centerId' => $this->center->id,
                'unsubscribed' => false,
            ],
        ];

        $parameters = $this->replaceInto($parameters, $parameterUpdates);
        $expectedResponse = $this->replaceInto($expectedResponse, $expectedResponseUpdates);

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    public function providerCreate()
    {
        return [
            // Required Parameters Only
            [[], []],
            // Additional Parameters
            [
                [ // Request
                    'data.isReviewer' => true,
                    'data.phone' => '555-555-1234',
                    'data.email' => 'peter.tests.a.lot@tmlpstats.com',
                ],
                [ // Response
                    'isReviewer' => true,
                    'person.phone' => '555-555-1234',
                    'person.email' => 'peter.tests.a.lot@tmlpstats.com',
                ],
            ],
        ];
    }

    public function testUpdate()
    {
        $parameters = [
            'method' => 'TeamMember.update',
            'teamMember' => $this->teamMember->id,
            'data' => [
                'phone' => '555-555-5678',
                'email' => 'testers@tmlpstats.com',
                'lastName' => 'McTester',
            ],
        ];

        $expectedResponse = $this->teamMember->load('person')->toArray();
        $expectedResponse['person']['phone'] = $parameters['data']['phone'];
        $expectedResponse['person']['email'] = $parameters['data']['email'];
        $expectedResponse['person']['lastName'] = $parameters['data']['lastName'];

        //\App::make(\TmlpStats\Api\TeamMember::class)->update($this->teamMember, $parameters['data']);

        $response = $this->post('/api', $parameters);
        $response->assertResponseStatus(200);
        $response->seeJsonHas($expectedResponse);
    }

}
