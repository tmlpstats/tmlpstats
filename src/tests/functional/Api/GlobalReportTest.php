<?php
namespace TmlpStats\Tests\Functional\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class GlobalReportTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();

        $this->courses = [];
        $this->courseData = [];

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();
        $this->quarter->setRegion($this->center->region);

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => '2016-04-15 18:55:00',
            'validated'      => 1,
            'version'        => 'test',
        ]);

        $this->globalReport = Models\GlobalReport::firstOrCreate([
            'reporting_date' => '2016-04-15',
        ]);

        $this->globalReport->addCenterReport($this->report);
    }

    public function generateCourses($report)
    {
        $course = factory(Models\Course::class)->create([
            'center_id'  => $report->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);
        $courseData = Models\CourseData::create([
            'course_id' => $course->id,
            'stats_report_id' => $report->id,
            'quarter_start_ter' => 10,
            'quarter_start_standard_starts' => 10,
            'quarter_start_xfer' => 0,
            'current_ter' => 35,
            'current_standard_starts' => 33,
            'current_xfer' => 2,
        ]);

        $pastCourse = factory(Models\Course::class)->create([
            'center_id'  => $report->center->id,
            'start_date' => Carbon::parse('2016-04-09'),
        ]);
        $pastCourseData = Models\CourseData::create([
            'course_id' => $pastCourse->id,
            'stats_report_id' => $report->id,
            'quarter_start_ter' => 10,
            'quarter_start_standard_starts' => 10,
            'quarter_start_xfer' => 0,
            'current_ter' => 35,
            'current_standard_starts' => 33,
            'current_xfer' => 2,
            'completed_standard_starts' => 32,
            'potentials' => 25,
            'registrations' => 23,
            'guests_promised' => 50,
            'guests_invited' => 45,
            'guests_confirmed' => 25,
            'guests_attended' => 15,
        ]);

        $this->courses[$report->center->name] = [
            $course,
            $pastCourse,
        ];

        $this->courseData[$report->center->name] = [
            $courseData,
            $pastCourseData,
        ];
    }

    public function testGetCourseList()
    {
        $parameters = [
            'method' => 'GlobalReport.getCourseList',
            'globalReport' => $this->globalReport->id,
            'region' => $this->center->region->parentId,
        ];

        $boston = Models\Center::abbreviation('BOS')->first();
        $bostonReport = Models\StatsReport::firstOrCreate([
            'center_id'      => $boston->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => '2016-04-15 18:57:00',
            'validated'      => 1,
            'version'        => 'test',
        ]);
        $this->globalReport->addCenterReport($bostonReport);

        $this->generateCourses($this->report);
        $this->generateCourses($bostonReport);

        $expectedResponse = [];
        foreach ([$this->center, $boston] as $center) {
            foreach ([0,1] as $i) {
                $course = $this->courses[$center->name][$i];
                $courseData = $this->courseData[$center->name][$i];

                $entry = $courseData->toArray();
                $entry['course'] = $course->toArray();

                $expectedResponse[] = $entry;
            }
        }

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }
}
