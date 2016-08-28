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

class ValidationDataObjectTest extends FunctionalTestAbstract
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
        $this->lastWeekCourseData = Models\CourseData::firstOrCreate([
            'course_id' => $this->course->id,
            'stats_report_id' => $this->lastReport->id,
            'quarter_start_ter' => 8,
            'quarter_start_standardStarts' => 6,
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

        App::make(Api\Course::class)->stash($this->center, $this->reportingDate, [
            'id' => $this->course2->id,
            'startDate' => $this->course2->startDate,
            'type' => $this->course2->type,
            'quarterStartTer' => 0,
            'quarterStartStandardStarts' => 0,
            'quarterStartXfer' => 0,
            'currentTer' => 23,
            'currentStandardStarts' => 19,
            'currentXfer' => 2,
        ]);

        $this->now = Carbon::parse("{$reportingDateStr} 18:45:00");
        Carbon::setTestNow($this->now);

        $this->context = MockContext::defaults()->withCenter($this->center)->install();
        $this->api = App::make(Api\ValidationData::class);
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testValidate_unauthorized()
    {
        $this->expectException(Api\Exceptions\UnauthorizedException::class);
        $this->api->validate($this->center, $this->now);
    }

    public function testGetSubmittedData()
    {
        $this->context->withFakedAdmin()->install();

        $submitted = $this->api->getSubmittedData($this->center, $this->reportingDate);

        $this->assertEquals(0, count($submitted['applications']));
        $this->assertEquals(0, count($submitted['scoreboard']));

        $this->assertEquals(1, count($submitted['courses']));
        $this->assertEquals($this->course2->id, $submitted['courses'][0]->getKey());
    }

    public function testGetUnsubmittedData()
    {
        $this->context->withFakedAdmin()->install();

        $unsubmitted = $this->api->getUnsubmittedData($this->center, $this->reportingDate);

        $this->assertEquals(0, count($unsubmitted['applications']));

        $this->assertEquals(2, count($unsubmitted['courses']));
        $this->assertEquals($this->course->id, $unsubmitted['courses'][0]->getKey());
        $this->assertEquals($this->course3->id, $unsubmitted['courses'][1]->getKey());

        $this->assertEquals(1, count($unsubmitted['scoreboard']));
        $this->assertEquals($this->reportingDate->toDateString(), $unsubmitted['scoreboard'][0]['week']);
    }
}
