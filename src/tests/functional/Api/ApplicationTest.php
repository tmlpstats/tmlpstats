<?php
namespace TmlpStats\Tests\Functional\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api\Exceptions as ApiExceptions;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class ApplicationTest extends FunctionalTestAbstract
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
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => null,
            'version'        => 'test',
        ]);

        $this->teamMember = factory(Models\TeamMember::class)->create([
            'incoming_quarter_id' => $this->lastQuarter->id,
        ]);
        $this->application = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-08'),
        ]);

        $this->applicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id'      => $this->report->id,
            'reg_date'             => $this->application->regDate,
        ]);
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
                'center'    => $this->center->id,
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
            'data' => [
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
    public function testGetWeekData($reportingDate = null)
    {
        $parameters = [
            'method'        => 'Application.getWeekData',
            'application'   => $this->application->id,
            'reportingDate' => $reportingDate,
        ];

        if (!$reportingDate) {
            // Passed into api as null, but will resolve to this
            $reportingDate = Carbon::parse('this friday', $this->center->timezone)
                                   ->startOfDay()
                                   ->toDateString();
        }

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
            [], // No date provided
        ];
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

    /**
     * @dataProvider providerAllForCenter
     */
    public function testAllForCenter($reportingDate = null)
    {
        $parameters = [
            'method' => 'Application.allForCenter',
            'center' => $this->center->id,
        ];
        if ($reportingDate) {
            $parameters['reportingDate'] = $reportingDate;
        }

        //
        // Last Week's Report
        //
        $lastWeeksReport = Models\StatsReport::create([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-08',
            'submitted_at'   => '2016-04-08 18:55:00',
            'version'        => 'test',
        ]);

        // Existing application's data for last week
        $app1LastWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id'      => $lastWeeksReport->id,
            'reg_date'             => $this->application->regDate,
            'comment'              => 'Last week',
        ]);

        // New person. Only has data last week
        $app2 = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-01'),
        ]);
        $app2LastWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $app2->id,
            'stats_report_id'      => $lastWeeksReport->id,
            'reg_date'             => $app2->regDate,
            'comment'              => 'Last week',
        ]);

        // Setup the global reports
        $lastWeeksGlobalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => '2016-04-08',
        ]);
        $lastWeeksGlobalReport->addCenterReport($lastWeeksReport);


        //
        // This Week's Report
        //
        $this->report->submittedAt = '2016-04-15 18:55:00';

        $thisWeeksGlobalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => '2016-04-15',
        ]);

        $thisWeeksGlobalReport->addCenterReport($this->report);

        //
        // Next Week's Report
        //
        $nextWeeksReport = Models\StatsReport::create([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-22',
            'submitted_at'   => '2016-04-22 18:55:00',
            'version'        => 'test',
        ]);

        // Existing application's data for last week
        $app1NextWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id'      => $nextWeeksReport->id,
            'reg_date'             => $this->application->regDate,
            'comment'              => 'Next week',
        ]);

        $app3 = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-15'),
        ]);
        $app3NextWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $app3->id,
            'stats_report_id'      => $lastWeeksReport->id,
            'reg_date'             => $app3->regDate,
            'comment'              => 'Next week',
        ]);

        // Setup the global reports
        $nextWeeksGlobalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => '2016-04-22',
        ]);

        $nextWeeksGlobalReport->addCenterReport($nextWeeksReport);

        // When a reporting date is provided, we get
        //      app1 with this week's data
        //      app2 with last week's data
        //
        // When no reporting date is provided, we get
        //      app1 with 'next' week's data
        //      app2 with last week's data
        //      app3 with 'next' week's data
        if ($reportingDate) {
            // Reporting Date provided
            $expectedResponse = [
                $this->applicationData->load('registration.person', 'statsReport')->toArray(),
                $app2LastWeekData->load('registration.person', 'statsReport')->toArray(),
            ];
        } else {
            // Reporting Date not provided
            $expectedResponse = [
                $app1NextWeekData->load('registration.person', 'statsReport')->toArray(),
                $app2LastWeekData->load('registration.person', 'statsReport')->toArray(),
                $app3NextWeekData->load('registration.person', 'statsReport')->toArray(),
            ];
        }

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    public function providerAllForCenter()
    {
        return [
            ['2016-04-15'], // Existing report
            [],// No report
        ];
    }
}
