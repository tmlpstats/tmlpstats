<?php
namespace TmlpStats\Tests\Functional\Api;

use Carbon\Carbon;
use Faker\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class CourseTest extends FunctionalTestAbstract
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
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => null,
            'version'        => 'test',
        ]);

        $this->pastCourse = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-09'),
        ]);

        $this->course = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);

        $this->courseData = Models\CourseData::firstOrCreate([
            'course_id'         => $this->course->id,
            'stats_report_id'   => $this->report->id,
            'quarter_start_ter' => 0,
            'quarter_start_standardStarts' => 0,
            'quarter_start_xfer'      => 0,
            'current_ter'             => 21,
            'current_standard_starts' => 18,
            'current_xfer'            => 2,
        ]);
    }

    /**
     * @dataProvider providerCreate
     */
    public function testCreate($parameterUpdates, $expectedResponseUpdates)
    {
        $parameters = [
            'method' => 'Course.create',
            'data'   => [
                'center'    => $this->center->id,
                'startDate' => Carbon::parse('2016-04-22'),
                'type'      => $this->faker->randomElement(['CAP', 'CPC']),
            ],
        ];

        $lastCourseId = Models\Course::count();

        $expectedResponse = [
            'id'        => $lastCourseId + 1,
            'centerId'  => $this->center->id,
            'startDate' => $parameters['data']['startDate']->toDateTimeString(),
            'type'      => $parameters['data']['type'],
            'center'    => $this->center->toArray(),
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
                    'data.location' => 'Another Place',
                ],
                [ // Response
                    'location' => 'Another Place',
                ],
            ],
        ];
    }

    public function testUpdate()
    {
        $parameters = [
            'method' => 'Course.update',
            'course' => $this->course->id,
            'data'   => [
                'type' => 'CPC',
                'location' => 'Another Place',
            ],
        ];

        $expectedResponse = $this->course->load('center')->toArray();
        $expectedResponse['location'] = $parameters['data']['location'];
        $expectedResponse['type'] = $parameters['data']['type'];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    /**
     * @dataProvider providerGetWeekData
     */
    public function testGetWeekData($reportingDate = null)
    {
        $parameters = [
            'method'        => 'Course.getWeekData',
            'course'        => $this->course->id,
            'reportingDate' => $reportingDate,
        ];

        if (!$reportingDate) {
            // Passed into api as null, but will resolve to this
            $reportingDate = Carbon::parse('this friday', $this->center->timezone)
                                   ->startOfDay()
                                   ->toDateString();
        }

        $report = $this->report->toArray();
        $courseDataId = $this->courseData->id;
        if ($reportingDate != $this->report->reportingDate->toDateString()) {
            $report['id'] += 1;
            $report['reportingDate'] = "{$reportingDate} 00:00:00";
            $report['version'] = 'api';

            $courseDataId = Models\CourseData::count() + 1;
        }

        $expectedResponse = [
            'id'            => $courseDataId,
            'courseId'      => $this->course->id,
            'statsReportId' => $report['id'],
            'course'        => $this->course->load('center')->toArray(),
            'statsReport'   => $report,
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
    public function testSetWeekData($reportingDate, $parameterUpdates, $expectedResponseUpdates)
    {
        $parameters = [
            'method' => 'Course.setWeekData',
            'course' => $this->course->id,
            'reportingDate' => $reportingDate,
            'data' => [
                'quarterStartTer' => 10,
                'quarterStartStandardStarts' => 10,
                'quarterStartXfer' => 0,
                'currentTer' => 35,
                'currentStandardStarts' => 33,
                'currentXfer' => 2,
            ],
        ];

        $report = $this->report->toArray();
        $course = $this->course;
        $courseDataId = $this->courseData->id;
        if ($reportingDate != $this->report->reportingDate->toDateString()) {
            $report['id'] += 1;
            $report['reportingDate'] = "{$reportingDate} 00:00:00";
            $report['version'] = 'api';

            $course = $this->pastCourse;
            $courseDataId = Models\CourseData::count() + 1;

            $parameterUpdates['course'] = $this->pastCourse->id;
        }

        $expectedResponse = [
            'id'                         => $courseDataId,
            'courseId'                   => $course->id,
            'statsReportId'              => $report['id'],
            'quarterStartTer'            => $parameters['data']['quarterStartTer'],
            'quarterStartStandardStarts' => $parameters['data']['quarterStartStandardStarts'],
            'quarterStartXfer'           => $parameters['data']['quarterStartXfer'],
            'currentTer'                 => $parameters['data']['currentTer'],
            'currentStandardStarts'      => $parameters['data']['currentStandardStarts'],
            'currentXfer'                => $parameters['data']['currentXfer'],
            'course'                     => $course->toArray(),
            'statsReport'                => $report,
        ];

        $parameters = $this->replaceInto($parameters, $parameterUpdates);
        $expectedResponse = $this->replaceInto($expectedResponse, $expectedResponseUpdates);

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }

    public function providerSetWeekData()
    {
        return [
            [ // Non-existent report
                '2016-04-08',
                [
                    'course' => 'update-me',
                    'data.completedStandardStarts' => 32,
                    'data.potentials' => 25,
                    'data.registrations' => 23,
                    'data.guestsPromised' => 50,
                    'data.guestsInvited' => 45,
                    'data.guestsConfirmed' => 25,
                    'data.guestsAttended' => 15,
                ],
                [],
            ],
            ['2016-04-15', [], []], // Existing report
        ];
    }

    /**
     * @dataProvider providerAllForCenter
     */
    public function testAllForCenter($reportingDate = null)
    {
        $parameters = [
            'method' => 'Course.allForCenter',
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

        // Existing course's data for last week
        $course1LastWeekData = Models\CourseData::create([
            'course_id'       => $this->course->id,
            'stats_report_id' => $lastWeeksReport->id,
            'quarter_start_ter'       => 0,
            'quarter_start_standardStarts' => 0,
            'quarter_start_xfer'      => 0,
            'current_ter'             => 18,
            'current_standard_starts' => 15,
            'current_xfer'            => 2,
        ]);

        // New person. Only has data last week
        $course2 = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-30'),
        ]);
        $course2LastWeekData = Models\CourseData::create([
            'course_id'       => $course2->id,
            'stats_report_id' => $lastWeeksReport->id,
            'quarter_start_ter'       => 15,
            'quarter_start_standardStarts' => 12,
            'quarter_start_xfer'      => 0,
            'current_ter'             => 28,
            'current_standard_starts' => 25,
            'current_xfer'            => 2,
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

        // Existing course's data for last week
        $course1NextWeekData = Models\CourseData::create([
            'course_id'       => $this->course->id,
            'stats_report_id' => $nextWeeksReport->id,
            'quarter_start_ter'       => 0,
            'quarter_start_standardStarts' => 0,
            'quarter_start_xfer'      => 0,
            'current_ter'             => 25,
            'current_standard_starts' => 22,
            'current_xfer'            => 2,
        ]);

        $course3 = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-05-21'),
        ]);
        $course3NextWeekData = Models\CourseData::create([
            'course_id'       => $course3->id,
            'stats_report_id' => $lastWeeksReport->id,
        ]);

        // Setup the global reports
        $nextWeeksGlobalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => '2016-04-22',
        ]);

        $nextWeeksGlobalReport->addCenterReport($nextWeeksReport);

        // When a reporting date is provided, we get
        //      course1 with this week's data
        //      course2 with last week's data
        //
        // When no reporting date is provided, we get
        //      course1 with 'next' week's data
        //      course2 with last week's data
        //      course3 with 'next' week's data
        if ($reportingDate) {
            // Reporting Date provided
            $expectedResponse = [
                $this->courseData->load('course', 'course.center', 'statsReport')->toArray(),
                $course2LastWeekData->load('course', 'course.center', 'statsReport')->toArray(),
            ];
        } else {
            // Reporting Date not provided
            $expectedResponse = [
                $course1NextWeekData->load('course', 'course.center', 'statsReport')->toArray(),
                $course2LastWeekData->load('course', 'course.center', 'statsReport')->toArray(),
                $course3NextWeekData->load('course', 'course.center', 'statsReport')->toArray(),
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
