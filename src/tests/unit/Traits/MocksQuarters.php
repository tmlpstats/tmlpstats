<?php
namespace TmlpStats\Tests\Traits;

use TmlpStats\Quarter;

trait MocksQuarters
{
    /**
     * Get a Quarter object mock
     *
     * Getters are mocked for all fields provided in $data
     *
     * @param array $methods
     * @param array $data
     *
     * @return mixed
     */
    protected function getQuarterMock($methods = [], $data = [])
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

        if (!$data) {
            return $quarter;
        }

        $startWeekendDate = isset($data['startWeekendDate'])
            ? $data['startWeekendDate']
            : null;

        $endWeekendDate = isset($data['endWeekendDate'])
            ? $data['endWeekendDate']
            : null;

        $classroom1Date = isset($data['classroom1Date'])
            ? $data['classroom1Date']
            : null;

        $classroom2Date = isset($data['classroom2Date'])
            ? $data['classroom2Date']
            : null;

        $classroom3Date = isset($data['classroom3Date'])
            ? $data['classroom3Date']
            : null;

        $firstWeekDate = isset($data['firstWeekDate'])
            ? $data['firstWeekDate']
            : null;

        if (!$firstWeekDate && $startWeekendDate) {
            $firstWeekDate = $startWeekendDate->copy();
            $firstWeekDate->addWeek();
        }

        $quarter->method('getFirstWeekDate')
                ->willReturn($firstWeekDate);

        $quarter->method('getQuarterStartDate')
                ->willReturn($startWeekendDate);

        $quarter->method('getQuarterEndDate')
                ->willReturn($endWeekendDate);

        $quarter->method('getClassroom1Date')
                ->willReturn($classroom1Date);

        $quarter->method('getClassroom2Date')
                ->willReturn($classroom2Date);

        $quarter->method('getClassroom3Date')
                ->willReturn($classroom3Date);

        $quarter->method('getQuarterDate')
                ->will($this->returnCallback(function ($field) use ($data) {
                    return isset($data[$field])
                        ? $data[$field]
                        : null;
                }));

        return $quarter;
    }
}
