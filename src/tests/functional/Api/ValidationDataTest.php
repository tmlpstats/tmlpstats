<?php
namespace TmlpStats\Tests\Functional\Api;

use App;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

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

        $reportingDateStr = '2016-04-15';
        $this->reportingDate = Carbon::parse($reportingDateStr);

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();
        $this->nextQuarter = Models\Quarter::year(2016)->quarterNumber(2)->first();
        $this->lastQuarter = Models\Quarter::year(2015)->quarterNumber(4)->first();

        $this->report = $this->getReport($reportingDateStr, ['submitted_at' => null]);
        $this->lastReport = $this->getReport('2016-04-08');
        $this->lastGlobalReport = $this->getGlobalReport('2016-04-08', [$this->lastReport]);

        // Setup course
        $this->course = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);
        $this->course2 = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-08-13'),
        ]);
        $this->course3 = factory(Models\Course::class)->create([
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

        $this->lastWeekApplicationData = Models\TmlpRegistrationData::firstOrCreate([
            'tmlp_registration_id' => $this->application->id,
            'stats_report_id' => $this->lastReport->id,
            'reg_date' => $this->application->regDate,
            'incoming_quarter_id' => $this->nextQuarter->id,
        ]);

        $this->lastWeekCourseData = Models\CourseData::firstOrCreate([
            'course_id' => $this->course->id,
            'stats_report_id' => $this->lastReport->id,
            'quarter_start_ter' => 8,
            'quarter_start_standard_starts' => 6,
            'quarter_start_xfer' => 0,
            'current_ter' => 28,
            'current_standard_starts' => 22,
            'current_xfer' => 2,
        ]);
        $this->lastWeekCourse2Data = Models\CourseData::firstOrCreate([
            'course_id' => $this->course2->id,
            'stats_report_id' => $this->lastReport->id,
            'quarter_start_ter' => 0,
            'quarter_start_standard_starts' => 0,
            'quarter_start_xfer' => 0,
            'current_ter' => 17,
            'current_standard_starts' => 17,
            'current_xfer' => 2,
        ]);
        $this->lastWeekCourse3Data = Models\CourseData::firstOrCreate([
            'course_id' => $this->course3->id,
            'stats_report_id' => $this->lastReport->id,
            'quarter_start_ter' => 0,
            'quarter_start_standard_starts' => 0,
            'quarter_start_xfer' => 0,
            'current_ter' => 8,
            'current_standard_starts' => 8,
            'current_xfer' => 0,
        ]);

        $this->now = Carbon::parse("{$reportingDateStr} 18:45:00");
        Carbon::setTestNow($this->now);

        $this->headers = ['Accept' => 'application/json'];
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
            'valid' => true,
            'messages' => [
                'TeamApplication' => [
                    ['level' => 'warning', 'id' => 'TEAMAPP_INCOMING_QUARTER_CHANGED'],
                ],
                'Course' => [ // changed from 0 -> 1
                    ['level' => 'warning', 'id' => 'COURSE_QSTART_XFER_CHANGED'],
                ],
                'Scoreboard' => [],
                'TeamMember' => [
                    ['level' => 'warning'],
                    ['level' => 'warning'],
                    ['level' => 'warning'],
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
                'cap' => 41,
                'cpc' => 0,
                't1x' => (int) ($this->application->teamYear == 1),
                't2x' => (int) ($this->application->teamYear == 2),
                'gitw' => 0,
                'lf' => 4,
            ],
        ];

        App::make(Api\Application::class)->stash($this->center, $reportingDate, $appData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData2);
        App::make(Api\Submission\Scoreboard::class)->stash($this->center, $reportingDate, $scoreboardData);

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
            'valid' => false,
            'messages' => [
                'Course' => [
                    [
                        'id' => 'COURSE_CURRENT_SS_GREATER_THAN_CURRENT_TER',
                        'reference' => [
                            'id' => $this->course->id,
                            'type' => 'Course',
                        ],
                    ],
                ],
                'Scoreboard' => [
                    [
                        'id' => 'GENERAL_MISSING_VALUE',
                        'reference' => [
                            'id' => $reportingDate->toDateString(),
                            'type' => 'Scoreboard',
                            'promiseType' => 'actual',
                            'game' => 'lf',
                        ],
                    ],
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
                'cap' => 41,
                'cpc' => 0,
                't1x' => (int) ($this->application->teamYear == 1),
                't2x' => (int) ($this->application->teamYear == 2),
                'gitw' => 0,
                // LF is missing
            ],
        ];

        App::make(Api\Application::class)->stash($this->center, $reportingDate, $appData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData);
        App::make(Api\Course::class)->stash($this->center, $reportingDate, $courseData2);
        App::make(Api\Submission\Scoreboard::class)->stash($this->center, $reportingDate, $scoreboardData);

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
        $this->post('/api', $parameters, $headers)
             ->seeJsonHas($expectedResponse)
             ->seeStatusCode(400);
    }
}
