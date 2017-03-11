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

        $this->report = $this->getReport('2016-04-15', ['submitted_at' => null]);

        $this->pastCourse = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-04-09'),
        ]);

        $this->course = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);

        $this->courseData = Models\CourseData::firstOrCreate([
            'course_id' => $this->course->id,
            'stats_report_id' => $this->report->id,
            'quarter_start_ter' => 0,
            'quarter_start_standardStarts' => 0,
            'quarter_start_xfer' => 0,
            'current_ter' => 21,
            'current_standard_starts' => 18,
            'current_xfer' => 2,
        ]);

        $this->headers = ['Accept' => 'application/json'];
    }

    /**
     * @dataProvider providerStash
     */
    public function testStash($reportingDate)
    {
        $parameters = [
            'method' => 'Course.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'id' => $this->course->id,
                'startDate' => $this->course->startDate,
                'type' => $this->course->type,
                'quarterStartTer' => 12,
                'quarterStartStandardStarts' => 12,
                'quarterStartXfer' => 0,
                'currentTer' => 24,
                'currentStandardStarts' => 22,
                'currentXfer' => 1,
            ],
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
            'success' => true,
            'valid' => true,
        ];

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\Course::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals(12, $result->quarterStartTer);
        $this->assertEquals(12, $result->quarterStartStandardStarts);
        $this->assertEquals(0, $result->quarterStartXfer);
        $this->assertEquals(24, $result->currentTer);
        $this->assertEquals(22, $result->currentStandardStarts);
        $this->assertEquals(1, $result->currentXfer);
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
        $reportingDate = '2016-04-15';

        $parameters = [
            'method' => 'Course.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'id' => $this->course->id,
                'startDate' => $this->course->startDate,
                'type' => $this->course->type,
                'quarterStartTer' => 12,
                'quarterStartStandardStarts' => 15,
                'quarterStartXfer' => 2,
                'currentTer' => 24,
                'currentStandardStarts' => 22,
                'currentXfer' => 1,
            ],
        ];

        if ($id) {
            $parameters['data']['id'] = $this->course->id;
        }

        $report = $this->report->toArray();
        $courseDataId = $this->courseData->id;

        $expectedResponse = [
            'success' => true,
            'valid' => false,
        ];

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\Course::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals(12, $result->quarterStartTer);
        $this->assertEquals(15, $result->quarterStartStandardStarts);
        $this->assertEquals(2, $result->quarterStartXfer);
        $this->assertEquals(24, $result->currentTer);
        $this->assertEquals(22, $result->currentStandardStarts);
        $this->assertEquals(1, $result->currentXfer);
    }

    public function providerStashFailsValidation()
    {
        return [
            ['id'], // Include application id
            [null], // Do not include application id
        ];
    }

    /**
     * @dataProvider providerStashFailsValidationWithMissingRequiredParameter
     */
    public function testStashFailsValidationWithMissingRequiredParameter($dropIndex)
    {
        $reportingDate = '2016-04-15';

        $data = [
            'id' => $this->course->id,
            'startDate' => $this->course->startDate,
            'type' => $this->course->type,
            'quarterStartTer' => 12,
            'quarterStartStandardStarts' => 12,
            'quarterStartXfer' => 0,
            'currentTer' => 24,
            'currentStandardStarts' => 22,
            'currentXfer' => 1,
        ];

        unset($data[$dropIndex]);

        $parameters = [
            'method' => 'Course.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => $data,
        ];

        $expectedResponse = [
            'success' => false,
        ];

        $this->post('/api', $parameters, $this->headers)
             ->seeJsonHas($expectedResponse)
             ->seeStatusCode(400);
    }

    public function providerStashFailsValidationWithMissingRequiredParameter()
    {
        return [
            ['startDate'],
            ['type'],
            ['quarterStartTer'],
            ['quarterStartStandardStarts'],
            ['quarterStartXfer'],
            ['currentTer'],
            ['currentStandardStarts'],
            ['currentXfer'],
        ];
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
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-08',
            'submitted_at' => '2016-04-08 18:55:00',
            'version' => 'test',
        ]);

        // Existing course's data for last week
        $course1LastWeekData = Models\CourseData::create([
            'course_id' => $this->course->id,
            'stats_report_id' => $lastWeeksReport->id,
            'quarter_start_ter' => 0,
            'quarter_start_standardStarts' => 0,
            'quarter_start_xfer' => 0,
            'current_ter' => 18,
            'current_standard_starts' => 15,
            'current_xfer' => 2,
        ]);

        // New person. Only has data last week
        $course2 = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-04-30'),
        ]);
        $course2LastWeekData = Models\CourseData::create([
            'course_id' => $course2->id,
            'stats_report_id' => $lastWeeksReport->id,
            'quarter_start_ter' => 15,
            'quarter_start_standardStarts' => 12,
            'quarter_start_xfer' => 0,
            'current_ter' => 28,
            'current_standard_starts' => 25,
            'current_xfer' => 2,
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

        // Existing course's data for last week
        $course1NextWeekData = Models\CourseData::create([
            'course_id' => $this->course->id,
            'stats_report_id' => $nextWeeksReport->id,
            'quarter_start_ter' => 0,
            'quarter_start_standardStarts' => 0,
            'quarter_start_xfer' => 0,
            'current_ter' => 25,
            'current_standard_starts' => 22,
            'current_xfer' => 2,
        ]);

        $course3 = factory(Models\Course::class)->create([
            'center_id' => $this->center->id,
            'start_date' => Carbon::parse('2016-05-21'),
        ]);
        $course3NextWeekData = Models\CourseData::create([
            'course_id' => $course3->id,
            'stats_report_id' => $nextWeeksReport->id,
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
                Domain\Course::fromModel($this->courseData),
                Domain\Course::fromModel($course2LastWeekData),
            ];
        } else {
            // Reporting Date not provided
            $expectedResponse = [
                Domain\Course::fromModel($course1NextWeekData),
                Domain\Course::fromModel($course2LastWeekData),
                Domain\Course::fromModel($course3NextWeekData),
            ];
        }

        $expectedResponse = json_decode(json_encode($expectedResponse), true);

        usort($expectedResponse, function ($a, $b) {
            return strcmp(
                $a['startDate'],
                $b['startDate']
            );
        });

        $this->post('/api', $parameters, $this->headers)->seeJsonHas($expectedResponse);
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
            'course' => $this->course->id,
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
            ['Course.allForCenter'],
            ['Course.stash'],
        ];
    }

    public function testApiThrowsExceptionForInvalidDateInStash()
    {
        $reportingDate = Carbon::parse('this thursday', $this->center->timezone)
            ->startOfDay()
            ->toDateString();

        $parameters = [
            'method' => 'Course.stash',
            'reportingDate' => $reportingDate,
            'center' => $this->center->id,
            'data' => [
                'id' => $this->course->id,
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
