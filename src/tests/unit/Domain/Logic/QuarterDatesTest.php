<?php
namespace TmlpStats\Tests\Unit\Domain\Logic;

use Carbon\Carbon;
use stdClass;
use TmlpStats\Domain\Logic\QuarterDates;
use TmlpStats\Tests\TestAbstract;

class QuarterDatesTest extends TestAbstract
{
    /**
     * @dataProvider providerParseQuarterDate
     */
    public function testParseQuarterDate($fakeQuarter, $input, $expectedDate)
    {
        $parsed = QuarterDates::parseQuarterDate($input, $fakeQuarter);
        $this->assertEquals(Carbon::parse($expectedDate), $parsed);
    }

    public function providerParseQuarterDate()
    {
        $q1 = $this->fakeQuarterLike([
            'startWeekendDate' => '2017-08-18',
            'classroom1Date' => '2017-08-25',
            'classroom2Date' => '2017-09-15',
            'classroom3Date' => '2017-10-27',
            'endWeekendDate' => '2017-11-17',
        ]);

        return [
            [$q1, 'classroom1Date', '2017-08-25'],
            [$q1, 'classroom2Date', '2017-09-15'],
            [$q1, 'classroom3Date', '2017-10-27'],
            [$q1, 'endWeekendDate', '2017-11-17'],
            [$q1, 'week1', '2017-08-25'],
            [$q1, 'week12', '2017-11-10'],
            [$q1, '2017-11-05', '2017-11-05'],
            [$q1, '2017-11-10', '2017-11-10'],
        ];
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage No quarter-like object provided
     */
    public function testParseQuarterDate__fail_noQuarter()
    {
        QuarterDates::parseQuarterDate('classroom2Date', null);
    }

    /**
     * @expectedException        Exception
     * @expectedExceptionMessage Invalid date format: abc-def-ghi
     */
    public function testParseQuarterDate__fail_badInput()
    {
        QuarterDates::parseQuarterDate('abc-def-ghi', new stdclass());
    }

    /**
     * @dataProvider providerGetNextMilestone
     */
    public function testGetNextMilestone($fakeQuarter, $refDateInput, $expectedDateInput)
    {

        $refDate = Carbon::parse($refDateInput);
        $milestone = QuarterDates::getNextMilestone($fakeQuarter, $refDate);
        $this->assertEquals(Carbon::parse($expectedDateInput), $milestone);
    }

    public function providerGetNextMilestone()
    {
        $q1 = $this->fakeQuarterLike([
            'classroom1Date' => '2017-08-25',
            'classroom2Date' => '2017-09-15',
            'classroom3Date' => '2017-10-27',
            'endWeekendDate' => '2017-11-17',
        ]);

        return [
            [$q1, '2017-08-01', '2017-08-25'],
            [$q1, '2017-08-25', '2017-08-25'],
            [$q1, '2017-08-26', '2017-09-15'],
            [$q1, '2017-09-15', '2017-09-15'],
            [$q1, '2017-09-16', '2017-10-27'],
            [$q1, '2017-10-28', '2017-11-17'],
            [$q1, '2017-12-05', '2017-11-17'],
        ];
    }

    protected function fakeQuarterLike(array $input): stdClass
    {
        $fakeQuarter = new stdClass();
        foreach ($input as $k => $d) {
            $fakeQuarter->$k = Carbon::parse($d)->startOfDay();
        }

        return $fakeQuarter;
    }

}
