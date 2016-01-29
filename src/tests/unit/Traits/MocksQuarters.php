<?php
namespace TmlpStats\Tests\Traits;

use TmlpStats\Quarter;

trait MocksQuarters
{
    protected function getQuarterMock($methods = [], $constructorArgs = [])
    {
        $defaultMethods = [
            'getFirstWeekDate',
            'getQuarterStartDate',
            'getQuarterEndDate',
            'getClassroom1Date',
            'getClassroom2Date',
            'getClassroom3Date',
            'getQuarterDate',
        ];
        $methods = $this->mergeMockMethods($defaultMethods, $methods);

        $quarter = $this->getMockBuilder(Quarter::class)
                        ->setMethods($methods)
                        ->getMock();

        if (!$constructorArgs) {
            return $quarter;
        }

        $startWeekendDate = isset($constructorArgs['startWeekendDate'])
            ? $constructorArgs['startWeekendDate']
            : null;

        $endWeekendDate = isset($constructorArgs['endWeekendDate'])
            ? $constructorArgs['endWeekendDate']
            : null;

        $classroom1Date = isset($constructorArgs['classroom1Date'])
            ? $constructorArgs['classroom1Date']
            : null;

        $classroom2Date = isset($constructorArgs['classroom2Date'])
            ? $constructorArgs['classroom2Date']
            : null;

        $classroom3Date = isset($constructorArgs['classroom3Date'])
            ? $constructorArgs['classroom3Date']
            : null;

        $firstWeekDate = isset($constructorArgs['firstWeekDate'])
            ? $constructorArgs['firstWeekDate']
            : null;

        if (!$firstWeekDate && $startWeekendDate) {
            $firstWeekDate = $startWeekendDate->copy();
            $firstWeekDate->addWeek();
        }

        $quarter->expects($this->any())
                ->method('getFirstWeekDate')
                ->will($this->returnValue($firstWeekDate));

        $quarter->expects($this->any())
                ->method('getQuarterStartDate')
                ->will($this->returnValue($startWeekendDate));

        $quarter->expects($this->any())
                ->method('getQuarterEndDate')
                ->will($this->returnValue($endWeekendDate));

        $quarter->expects($this->any())
                ->method('getClassroom1Date')
                ->will($this->returnValue($classroom1Date));

        $quarter->expects($this->any())
                ->method('getClassroom2Date')
                ->will($this->returnValue($classroom2Date));

        $quarter->expects($this->any())
                ->method('getClassroom3Date')
                ->will($this->returnValue($classroom3Date));

        $quarter->expects($this->any())
                ->method('getQuarterDate')
                ->will($this->returnCallback(function() use ($constructorArgs) {
                    $args = func_get_args();
                    $this->assertEquals(2, count($args));

                    return isset($constructorArgs[$args[0]])
                        ? $constructorArgs[$args[0]]
                        : null;
                }));

        return $quarter;
    }
}
