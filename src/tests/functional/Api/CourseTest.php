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

        $this->course = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);

        $this->pastCourse = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-09'),
        ]);

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => null,
            'version'        => 'test',
        ]);

        $this->courseData = Models\CourseData::firstOrCreate([
            'course_id'       => $this->course->id,
            'stats_report_id' => $this->report->id,
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
    public function testGetWeekData($reportingDate)
    {
        $parameters = [
            'method'        => 'Course.getWeekData',
            'course'        => $this->course->id,
            'reportingDate' => $reportingDate,
        ];

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
            ['2016-04-15', [], []], // Non-existent report
            [ // Existing report
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
        ];
    }

    public function testSetWeekDataReturns400WhenQuarterNotFound()
    {
        // Test that the exceptions is thrown properly
        $this->markTestIncomplete('Not yet implemented');
    }
}
