<?php
namespace TmlpStats\Tests;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;

class ApplicationTest extends TestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->now = Carbon::now();
        Carbon::setTestNow($this->now);

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();
        $this->lastQuarter = Models\Quarter::year(2015)->quarterNumber(4)->first();
        $this->nextQuarter = Models\Quarter::year(2016)->quarterNumber(2)->first();

        $this->teamMember = factory(Models\TeamMember::class)->create([
            'incoming_quarter_id' => $this->lastQuarter->id,
        ]);
        $this->application = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-08'),
        ]);

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => null,
            'version'        => 'test',
        ]);

        $this->applicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id'      => $this->report->id,
        ]);

        $this->user = factory(Models\User::class)->create();
        $this->be($this->user);
    }

    public function tearDown()
    {
        parent::tearDown();

        // Reset testing datetime
        Carbon::setTestNow();
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate($parameterUpdates, $expectedResponseUpdates)
    {
        $parameters = [
            'method' => 'Application.create',
            'data'   => [
                'firstName' => $this->faker->firstName(),
                'lastName'  => $this->faker->lastName(),
                'center'  => $this->center->id,
                'teamYear'  => 2,
                'regDate'   => '2016-04-15',
            ],
        ];

        $lastPersonId = Models\Person::count();
        $lastApplicationId = Models\TmlpRegistration::count();

        $expectedResponse = [
            'id'         => $lastApplicationId + 1,
            'regDate'    => "{$parameters['data']['regDate']} 00:00:00",
            'teamYear'   => $parameters['data']['teamYear'],
            'personId'   => $lastPersonId + 1,
            'isReviewer' => false,
            'person'     => [
                'id'           => $lastPersonId + 1,
                'firstName'    => $parameters['data']['firstName'],
                'lastName'     => $parameters['data']['lastName'],
                'phone'        => null,
                'email'        => null,
                'centerId'     => $this->center->id,
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
                    'data.phone'      => '555-555-1234',
                    'data.email'      => 'peter.tests.a.lot@tmlpstats.com',
                ],
                [ // Response
                    'isReviewer'   => true,
                    'person.phone' => '555-555-1234',
                    'person.email' => 'peter.tests.a.lot@tmlpstats.com',
                ],
            ],
        ];
    }

    public function testUpdate()
    {
        $parameters = [
            'method'      => 'Application.update',
            'application' => $this->application->id,
            'data'        => [
                'phone'    => '555-555-5678',
                'email'    => 'testers@tmlpstats.com',
                'lastName' => 'McTester',
            ],
        ];

        $expectedResponse = $this->application->load('person')->toArray();
        $expectedResponse['person']['phone'] = $parameters['data']['phone'];
        $expectedResponse['person']['email'] = $parameters['data']['email'];
        $expectedResponse['person']['lastName'] = $parameters['data']['lastName'];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    /**
     * @dataProvider providerGetWeekData
     */
    public function testGetWeekData($reportingDate)
    {
        $parameters = [
            'method'        => 'Application.getWeekData',
            'application'   => $this->application->id,
            'reportingDate' => $reportingDate,
        ];

        $report = $this->report->toArray();
        $applicationDataId = $this->applicationData->id;
        if ($reportingDate != $this->report->reportingDate->toDateString()) {
            $report['id'] += 1;
            $report['reportingDate'] = "{$reportingDate} 00:00:00";
            $report['version'] = 'api';

            $applicationDataId = Models\TmlpRegistrationData::count() + 1;
        }

        $expectedResponse = [
            'tmlpRegistrationId'  => $this->application->id,
            'id'                  => $applicationDataId,
            'registration'        => $this->application->toArray(),
            'incomingQuarter'     => null,
            'withdrawCode'        => null,
            'committedTeamMember' => null,
            'statsReportId'       => $report['id'],
            'statsReport'         => $report,
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    public function providerGetWeekData()
    {
        return [
            ['2016-04-08'], // Non-existent report
            ['2016-04-15'], // Existing report
        ];
    }

    public function testGetWeekDataReturns400WhenQuarterNotFound()
    {
        // Test that the exceptions is thrown properly
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * @dataProvider providerSetWeekData
     */
    public function testSetWeekData($reportingDate)
    {
        $parameters = [
            'method'        => 'Application.setWeekData',
            'application'   => $this->application->id,
            'reportingDate' => $reportingDate,
            'data'          => [
                'appOutDate'            => '2016-04-17',
                'apprDate'              => '2016-04-23',
                'committedTeamMemberId' => 1,
            ],
        ];

        $report = $this->report->toArray();
        $applicationDataId = $this->applicationData->id;
        if ($reportingDate != $this->report->reportingDate->toDateString()) {
            $report['id'] += 1;
            $report['reportingDate'] = "{$reportingDate} 00:00:00";
            $report['version'] = 'api';

            $applicationDataId = Models\TmlpRegistrationData::count() + 1;
        }

        $expectedResponse = [
            'tmlpRegistrationId'  => $this->application->id,
            'id'                  => $applicationDataId,
            'appOutDate'          => "{$parameters['data']['appOutDate']} 00:00:00",
            'apprDate'            => "{$parameters['data']['apprDate']} 00:00:00",
            'regDate'             => $this->application->regDate,
            'registration'        => $this->application->toArray(),
            'incomingQuarter'     => null,
            'withdrawCode'        => null,
            'committedTeamMember' => null,
            'statsReportId'       => $report['id'],
            'statsReport'         => $report,
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    public function providerSetWeekData()
    {
        return [
            ['2016-04-08'], // Non-existent report
            ['2016-04-15'], // Existing report
        ];
    }

    public function testSetWeekDataReturns400WhenQuarterNotFound()
    {
        // Test that the exceptions is thrown properly
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * Takes a multidimensional array, and replaces values using the replace array.
     *
     * Replace should use the dot notation similar to what's returned by calling array_dot($array);
     *
     * @param $array    array with hierarchical data
     * @param $replace  array with dotted replacement data
     *
     * @return mixed
     */
    public function replaceInto($array, $replace)
    {
        foreach ($replace as $key => $value) {
            array_set($array, $key, $value);
        }

        return $array;
    }

    /**
     * Check if $expected exists within the actual result. Matches array hierarchy
     *
     * @param array|null $expected
     *
     * @return $this|void
     */
    public function seeJsonHas(array $expected = null)
    {
        if (is_null($expected)) {
            $this->assertJson(
                $this->response->getContent(), "JSON was not returned from [{$this->currentUri}]."
            );

            return $this;
        }

        $actual = json_decode($this->response->getContent(), true);
        if (is_null($actual) || $actual === false) {
            return $this->fail('Invalid JSON was returned from the route. Perhaps an exception was thrown?');
        }

        $expected = array_dot($expected);
        foreach ($expected as $key => $value) {
            $this->assertEquals($value, array_get($actual, $key), "Unable to find JSON fragment actual[{$key}] = {$value} within [" . print_r($actual, true) . "].");
        }

        return $this;
    }
}
