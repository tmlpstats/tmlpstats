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

class ValidationDataTest extends FunctionalTestAbstract
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

        $this->report = $this->getReport('2016-04-15', ['submitted_at' => null]);

        // Setup course
        $this->course = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);
        $this->course2 = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-08-13'),
        ]);

        // Setup application
        $this->teamMember = factory(Models\TeamMember::class)->create([
            'incoming_quarter_id' => $this->lastQuarter->id,
        ]);
        $this->application = factory(Models\TmlpRegistration::class)->create([
            'reg_date' => Carbon::parse('2016-04-01'),
        ]);

        $this->now = Carbon::parse('2016-04-15 18:45:00');
        Carbon::setTestNow($this->now);

        $this->headers = ['Accept' => 'application/json'];
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testValidateSucceeds()
    {
        $reportingDate = $this->report->reportingDate;
        $parameters = [
            'method' => 'ValidationData.validate',
            'center' => $this->center->id,
            'reportingDate' => $reportingDate,
        ];

        $expectedResponse = [
            'success' => true,
            'results' => [
                'applications' => [
                    ['valid' => true]
                ],
                'courses' => [
                    ['valid' => true],
                    ['valid' => true],
                ],
                'scoreboard' => [
                    ['valid' => true]
                ],
            ],
        ];

        $appData = [
            'id' => $this->application->id,
            'regDate' => $this->application->regDate,
            'appOutDate' => '2016-04-02',
            'appInDate' => '2016-04-03',
            'apprDate' => '2016-04-11',
            'committedTeamMember' => $this->teamMember->id,
            'incomingQuarter' => $this->quarter->id,
        ];

        $courseData = [
            'id' => $this->course->id,
            'startDate' => $this->course->startDate,
            'type' => $this->course->type,
            'quarterStartTer' => 8,
            'quarterStartStandardStarts' => 6,
            'quarterStartXfer' => 1,
            'currentTer' => 28,
            'currentStandardStarts' => 22,
            'currentXfer' => 2,
        ];

        $courseData2 = [
            'id' => $this->course2->id,
            'startDate' => $this->course2->startDate,
            'type' => $this->course2->type,
            'quarterStartTer' => 0,
            'quarterStartStandardStarts' => 0,
            'quarterStartXfer' => 0,
            'currentTer' => 17,
            'currentStandardStarts' => 17,
            'currentXfer' => 2,
        ];

        $scoreboardData = [
            'week' => $reportingDate->toDateString(),
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
        ];

        App::make(Api\Application::class)->stash($this->center, $reportingDate, $appData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData2);
        App::make(Api\Scoreboard::class)->stash($this->center, $reportingDate, $scoreboardData);

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
    }

    public function testValidateFails()
    {
        $reportingDate = $this->report->reportingDate;
        $parameters = [
            'method' => 'ValidationData.validate',
            'center' => $this->center->id,
            'reportingDate' => $reportingDate,
        ];

        $expectedResponse = [
            'success' => true,
            'results' => [
                'applications' => [
                    ['valid' => true]
                ],
                'courses' => [
                    ['valid' => false],
                    ['valid' => true],
                ],
                'scoreboard' => [
                    ['valid' => false]
                ],
            ],
        ];

        $appData = [
            'id' => $this->application->id,
            'regDate' => $this->application->regDate,
            'appOutDate' => '2016-04-02',
            'appInDate' => '2016-04-03',
            'apprDate' => '2016-04-11',
            'committedTeamMember' => $this->teamMember->id,
            'incomingQuarter' => $this->quarter->id,
        ];

        $courseData = [
            'id' => $this->course->id,
            'startDate' => $this->course->startDate,
            'type' => $this->course->type,
            'quarterStartTer' => 8,
            'quarterStartStandardStarts' => 6,
            'quarterStartXfer' => 1,
            'currentTer' => 28,
            'currentStandardStarts' => 30,
            'currentXfer' => 2,
        ];

        $courseData2 = [
            'id' => $this->course2->id,
            'startDate' => $this->course2->startDate,
            'type' => $this->course2->type,
            'quarterStartTer' => 0,
            'quarterStartStandardStarts' => 0,
            'quarterStartXfer' => 0,
            'currentTer' => 17,
            'currentStandardStarts' => 17,
            'currentXfer' => 2,
        ];

        $scoreboardData = [
            'week' => $reportingDate->toDateString(),
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
            ],
        ];

        App::make(Api\Application::class)->stash($this->center, $reportingDate, $appData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData2);
        App::make(Api\Scoreboard::class)->stash($this->center, $reportingDate, $scoreboardData);

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
    }

    public function testApiThrowsExceptionForInvalidDate()
    {
        $reportingDate = Carbon::parse('this thursday', $this->center->timezone)
            ->startOfDay()
            ->toDateString();

        $parameters = [
            'method' => 'ValidationData.validate',
            'center' => $this->center->id,
            'reportingDate' => $reportingDate,
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
