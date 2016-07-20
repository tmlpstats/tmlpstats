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
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at' => null,
            'version' => 'test',
        ]);

        $this->teamMember = factory(Models\TeamMember::class)->create([
            'incoming_quarter_id' => $this->lastQuarter->id,
        ]);
        $this->application = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-08'),
        ]);

        $this->applicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id' => $this->report->id,
            'reg_date' => $this->application->regDate,
        ]);
    }

    public function tearDown()
    {
        Carbon::setTestNow(); // Clear test now
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate($parameterUpdates, $expectedResponseUpdates)
    {
        $parameters = [
            'method' => 'Application.create',
            'data' => [
                'firstName' => $this->faker->firstName(),
                'lastName' => $this->faker->lastName(),
                'center' => $this->center->id,
                'teamYear' => 2,
                'regDate' => '2016-04-15',
            ],
        ];

        $lastPersonId = Models\Person::count();
        $lastApplicationId = Models\TmlpRegistration::count();

        $expectedResponse = [
            'id' => $lastApplicationId + 1,
            'regDate' => "{$parameters['data']['regDate']} 00:00:00",
            'teamYear' => $parameters['data']['teamYear'],
            'personId' => $lastPersonId + 1,
            'isReviewer' => false,
            'person' => [
                'id' => $lastPersonId + 1,
                'firstName' => $parameters['data']['firstName'],
                'lastName' => $parameters['data']['lastName'],
                'phone' => null,
                'email' => null,
                'centerId' => $this->center->id,
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
            'method' => 'Application.update',
            'application' => $this->application->id,
            'data' => [
                'phone' => '555-555-5678',
                'email' => 'testers@tmlpstats.com',
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
            'method' => 'Application.getWeekData',
            'application' => $this->application->id,
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
            'tmlpRegistrationId' => $this->application->id,
            'id' => $applicationDataId,
            'registration' => $this->application->toArray(),
            'incomingQuarter' => null,
            'withdrawCode' => null,
            'committedTeamMember' => null,
            'statsReportId' => $report['id'],
            //'statsReport' => $report,
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
     * @dataProvider providerStash
     */
    public function testStash($reportingDate)
    {
        $parameters = [
            'method' => 'Application.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'tmlpRegistration' => $this->application->id,
                'appOutDate' => '2016-04-09',
                'appInDate' => '2016-04-10',
                'apprDate' => '2016-04-11',
                'committedTeamMember' => 1,
                'teamYear' => 1,
                'incomingQuarter' => $this->quarter->id,
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
            'success' => true,
            'valid' => true,
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\TeamApplication::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals('2016-04-09', $result->appOutDate->toDateString());
        $this->assertEquals('2016-04-10', $result->appInDate->toDateString());
        $this->assertEquals('2016-04-11', $result->apprDate->toDateString());
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

        $parameters = [
            'method' => 'Application.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'tmlpRegistration' => $this->application->id,
                'appOutDate' => '2016-04-09',
                'appInDate' => '2016-04-10',
                'apprDate' => '2016-04-08',
                'committedTeamMember' => 1,
                'teamYear' => 1,
                'incomingQuarter' => $this->quarter->id,
            ],
        ];

        $report = $this->report->toArray();
        $applicationDataId = $this->applicationData->id;

        $expectedResponse = [
            'success' => true,
            'valid' => false,
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\TeamApplication::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals('2016-04-09', $result->appOutDate->toDateString());
        $this->assertEquals('2016-04-10', $result->appInDate->toDateString());
        $this->assertEquals('2016-04-08', $result->apprDate->toDateString());
    }

    /**
     * @dataProvider providerAllForCenter
     */
    public function testAllForCenter($reportingDate = null)
    {
        if (!$reportingDate) {
            Carbon::setTestNow(Carbon::create(2016, 05, 20));
        }
        $parameters = [
            'method' => 'Application.allForCenter',
            'center' => $this->center->id,
            'includeInProgress' => false,
        ];
        if ($reportingDate) {
            $parameters['reportingDate'] = $reportingDate;
        }

        //
        // Last Week's Report
        //
        $lastWeeksReport = Models\StatsReport::create([
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-08',
            'submitted_at' => '2016-04-08 18:55:00',
            'version' => 'test',
        ]);

        // Existing application's data for last week
        $app1LastWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id' => $lastWeeksReport->id,
            'reg_date' => $this->application->regDate,
            'comment' => 'Last week',
        ]);

        // New person. Only has data last week
        $app2 = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-01'),
        ]);
        $app2LastWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $app2->id,
            'stats_report_id' => $lastWeeksReport->id,
            'reg_date' => $app2->regDate,
            'comment' => 'Last week',
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
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-22',
            'submitted_at' => '2016-04-22 18:55:00',
            'version' => 'test',
        ]);

        // Existing application's data for last week
        $app1NextWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id' => $nextWeeksReport->id,
            'reg_date' => $this->application->regDate,
            'comment' => 'Next week',
        ]);

        $app3 = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-15'),
        ]);
        $app3NextWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $app3->id,
            'stats_report_id' => $nextWeeksReport->id,
            'reg_date' => $app3->regDate,
            'comment' => 'Next week',
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
                Domain\TeamApplication::fromModel($this->applicationData),
                Domain\TeamApplication::fromModel($app2LastWeekData),
            ];
        } else {
            // Reporting Date not provided
            $expectedResponse = [
                Domain\TeamApplication::fromModel($app1NextWeekData),
                Domain\TeamApplication::fromModel($app2LastWeekData),
                Domain\TeamApplication::fromModel($app3NextWeekData),
            ];
        }

        $expectedResponse = json_decode(json_encode($expectedResponse), true);

        usort($expectedResponse, function ($a, $b) {
            return strcmp(
                $a['firstName'],
                $b['firstName']
            );
        });

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    public function providerAllForCenter()
    {
        return [
            ['2016-04-15'], // Existing report
            [null], // No report
        ];
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
            'application' => $this->application->id,
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

        $headers = ['Accept' => 'application/json'];
        $this->post('/api', $parameters, $headers)->seeJsonHas($expectedResponse);
    }

    public function providerApiThrowsExceptionForInvalidDate()
    {
        return [
            ['Application.allForCenter'],
            ['Application.getWeekData'],
            // ['Application.stash'],
        ];
    }


    public function testApiThrowsExceptionForInvalidDateInStash()
    {
        $reportingDate = Carbon::parse('this thursday', $this->center->timezone)
            ->startOfDay()
            ->toDateString();

        $parameters = [
            'method' => 'Application.stash',
            'reportingDate' => $reportingDate,
            'center' => $this->center->id,
            'data' => [
                'tmlpRegistration' => $this->application->id,
            ],
        ];

        $expectedResponse = [
            'success' => false,
            'error' => [
                'message' => 'Reporting date must be a Friday.',
            ],
        ];

        $headers = ['Accept' => 'application/json'];
        $this->post('/api', $parameters, $headers)->seeJsonHas($expectedResponse);
    }
}
