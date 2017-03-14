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
            'comment' => 'This week',
        ]);

        $this->headers = ['Accept' => 'application/json'];
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
                'id' => $this->application->id,
                'appOutDate' => '2016-04-09',
                'appInDate' => '2016-04-10',
                'apprDate' => '2016-04-11',
                'committedTeamMember' => 1,
                'teamYear' => 1,
                'incomingQuarter' => $this->quarter->id,
                'committedTeamMember' => $this->teamMember->id,
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

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
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

    /**
     * @dataProvider providerStashFailsValidation
     */
    public function testStashFailsValidation($id)
    {
        $isNew = $id === null;

        $reportingDate = '2016-04-15';

        $parameters = [
            'method' => 'Application.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'appOutDate' => '2016-04-09',
                'appInDate' => '2016-04-10',
                'apprDate' => '2016-04-08',
                'committedTeamMember' => 1,
                'teamYear' => 1,
                'incomingQuarter' => $this->quarter->id,
                'committedTeamMember' => $this->teamMember->id,
            ],
        ];

        if (!$isNew) {
            $parameters['data']['id'] = $this->application->id;
        }

        $report = $this->report->toArray();
        $applicationDataId = $this->applicationData->id;

        $expectedResponse = [
            'success' => !$isNew,
            'valid' => false,
        ];

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\TeamApplication::class);

        $this->assertEquals($isNew ? 0 : 1, count($result1));
        if (!$isNew) {
            $result = $result1[0];
            $this->assertEquals('2016-04-09', $result->appOutDate->toDateString());
            $this->assertEquals('2016-04-10', $result->appInDate->toDateString());
            $this->assertEquals('2016-04-08', $result->apprDate->toDateString());
        }
    }

    public function providerStashFailsValidation()
    {
        return [
            ['id'], // Include application id
            [null], // Do not include application id
        ];
    }

    public function testStashFailsValidationWithMissingRequiredParameter()
    {
        $reportingDate = '2016-04-15';

        $parameters = [
            'method' => 'Application.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'id' => $this->application->id,
            ],
        ];

        $expectedResponse = [
            'success' => false,
        ];

        $this->post('/api', $parameters, $this->headers)
             ->seeJsonHas($expectedResponse)
             ->seeStatusCode(400);
    }

    /**
     * @dataProvider providerAllForCenter
     */
    public function testAllForCenter($reportingDate)
    {
        $parameters = [
            'method' => 'Application.allForCenter',
            'center' => $this->center->id,
            'reportingDate' => $reportingDate,
            'includeInProgress' => false,
        ];

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

        $app2ThisWeekData = Models\TmlpRegistrationData::create([
            'tmlp_registration_id' => $app2->id,
            'stats_report_id' => $this->report->id,
            'reg_date' => $app2->regDate,
            'comment' => 'This week',
        ]);

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

        // When last reporting date is provided, we get
        //      app1 with last week's data
        //      app2 with last week's data
        //
        // When this reporting date is provided, we get
        //      app1 with this week's data
        //      app2 with last week's data
        //      app3 not included
        if ($reportingDate == '2016-04-08') {
            // Reporting Date provided
            $expectedResponse = [
                $this->application->id => Domain\TeamApplication::fromModel($app1LastWeekData),
                $app2->id => Domain\TeamApplication::fromModel($app2LastWeekData),
            ];
        } else {
            // Reporting Date not provided
            $expectedResponse = [
                $this->application->id => Domain\TeamApplication::fromModel($this->applicationData),
                $app2->id => Domain\TeamApplication::fromModel($app2ThisWeekData),
            ];
        }
        foreach ($expectedResponse as &$o) {
            $o->meta['fromReport'] = true;
        }
        $expectedResponse = json_decode(json_encode($expectedResponse), true);

        $this->json('POST', '/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
    }

    public function providerAllForCenter()
    {
        return [
            ['2016-04-08'],
            ['2016-04-15'],
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

        $this->post('/api', $parameters, $this->headers)
             ->seeJsonHas($expectedResponse)
             ->seeStatusCode(400);
    }

    public function providerApiThrowsExceptionForInvalidDate()
    {
        return [
            ['Application.allForCenter'],
            ['Application.stash'],
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
                'id' => $this->application->id,
            ],
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
}
