<?php
namespace TmlpStats\Tests\Functional\Api;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class LocalReportTest extends FunctionalTestAbstract
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
        $this->quarter->setRegion($this->center->region);

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id'      => $this->center->id,
            'quarter_id'     => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at'   => null,
            'version'        => 'test',
        ]);
    }

    public function generateScoreboard($center, Carbon $reportingDate)
    {
        $scoreboard = [];
        $reports = [];

        $weekNumber = 1;
        $date = $this->quarter->getFirstWeekDate($center);
        while ($date->lte($this->quarter->endWeekendDate)) {

            $promiseValue = $weekNumber * 2;

            if ($date->lte($reportingDate)) {
                $globalReport = Models\GlobalReport::firstOrCreate([
                    'reporting_date' => $date,
                ]);

                $report = Models\StatsReport::firstOrCreate([
                    'center_id'      => $center->id,
                    'quarter_id'     => $this->quarter->id,
                    'reporting_date' => $date->toDateString(),
                    'validated'      => true,
                    'submitted_at'   => $date->toDateTimeString(),
                    'version'        => 'test',
                ]);

                $globalReport->addCenterReport($report);

                $scoreboard[$date->toDateString()]['actual'] = Models\CenterStatsData::firstOrCreate([
                    'reporting_date' => $date->toDateString(),
                    'stats_report_id' => $report->id,
                    'type' => 'actual',
                    'cap' => $promiseValue - 1,
                    'cpc' => $promiseValue - 1,
                    't1x' => $promiseValue - 1,
                    't2x' => $promiseValue - 1,
                    'gitw' => 80 + $weekNumber - 1,
                    'lf' => $promiseValue - 1,
                    'tdo' => mt_rand(25, 100),
                ]);
            }

            $reports[] = $report;

            $scoreboard[$date->toDateString()]['promise'] = Models\CenterStatsData::firstOrCreate([
                'reporting_date' => $date->toDateString(),
                'stats_report_id' => $reports[0]->id, // Always attached to week 1
                'type' => 'promise',
                'cap' => $promiseValue,
                'cpc' => $promiseValue,
                't1x' => $promiseValue,
                't2x' => $promiseValue,
                'gitw' => 80 + $weekNumber,
                'lf' => $promiseValue,
                'tdo' => 100,
            ]);

            $weekNumber++;
            $date->addWeek();
        }

        return $scoreboard;
    }

    public function testGetCurrentScore()
    {
        $this->markTestIncomplete('Test no fully implemented');

        $parameters = [
            'method' => 'LocalReport.getQuarterScoreboard',
            'localReport' => $this->report->id,
            //'options' => ['returnUnprocessed' => true]
        ];

        $scoreboard = $this->generateScoreboard($this->center, $this->report->reportingDate);

        $this->post('/api', $parameters)->dump();//seeJsonHas($expectedResponse);
    }

    public function testGetCourseList()
    {
        $course = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-23'),
        ]);
        $courseData = Models\CourseData::create([
            'course_id' => $course->id,
            'stats_report_id' => $this->report->id,
            'quarter_start_ter' => 10,
            'quarter_start_standard_starts' => 10,
            'quarter_start_xfer' => 0,
            'current_ter' => 35,
            'current_standard_starts' => 33,
            'current_xfer' => 2,
        ]);

        $pastCourse = factory(Models\Course::class)->create([
            'center_id'  => $this->center->id,
            'start_date' => Carbon::parse('2016-04-09'),
        ]);
        $pastCourseData = Models\CourseData::create([
            'course_id' => $pastCourse->id,
            'stats_report_id' => $this->report->id,
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

        $parameters = [
            'method' => 'LocalReport.getCourseList',
            'localReport' => $this->report->id,
        ];

        $expectedResponse = [
            [
                'id' => $courseData->id,
                'courseId' => $course->id,
                'quarterStartTer' => 10,
                'quarterStartStandardStarts' => 10,
                'quarterStartXfer' => 0,
                'currentTer' => 35,
                'currentStandardStarts' => 33,
                'currentXfer' => 2,
                'completedStandardStarts' => null,
                'potentials' => null,
                'registrations' => null,
                'guestsPromised' => null,
                'guestsInvited' => null,
                'guestsConfirmed' => null,
                'guestsAttended' => null,
                'statsReportId' => $this->report->id,
                'course' => $course->toArray(),
            ],
            [
                'id' => $pastCourseData->id,
                'courseId' => $pastCourse->id,
                'quarterStartTer' => 10,
                'quarterStartStandardStarts' => 10,
                'quarterStartXfer' => 0,
                'currentTer' => 35,
                'currentStandardStarts' => 33,
                'currentXfer' => 2,
                'completedStandardStarts' => 32,
                'potentials' => 25,
                'registrations' => 23,
                'guestsPromised' => 50,
                'guestsInvited' => 45,
                'guestsConfirmed' => 25,
                'guestsAttended' => 15,
                'statsReportId' => $this->report->id,
                'course' => $pastCourse->toArray(),
            ],
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);
    }
}
