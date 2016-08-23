<?php
namespace TmlpStats\Tests\Unit\Settings;

use Carbon\Carbon;
use TmlpStats\Center;
use TmlpStats\Settings\ReportDeadlines;
use TmlpStats\StatsReport;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Unit\Traits\MocksQuarters;
use TmlpStats\Tests\Unit\Traits\MocksSettings;

class ReportDeadlinesTest extends TestAbstract
{
    use MocksSettings, MocksQuarters;

    protected $testClass = StatsReport::class;

    public function tearDown()
    {
        parent::tearDown();

        $this->clearSettings();
    }

    /**
     * @dataProvider providerGet
     */
    public function testGet($setting, $reportingDate, $quarterDates, $center, $expectedResponse)
    {
        $this->setSetting('reportDeadlines', $setting);

        $quarter = $this->getQuarterMock([], $quarterDates);

        $result = ReportDeadlines::get($center, $quarter, $reportingDate);

        $this->assertEquals($expectedResponse, $result);
    }

    public function providerGet()
    {
        $quarterDates = [
            'startWeekendDate' => Carbon::createFromDate(2015, 11, 20)->startOfDay(),
            'classroom1Date'   => Carbon::createFromDate(2015, 12, 4)->startOfDay(),
            'classroom2Date'   => Carbon::createFromDate(2016, 1, 8)->startOfDay(),
            'classroom3Date'   => Carbon::createFromDate(2016, 2, 5)->startOfDay(),
            'endWeekendDate'   => Carbon::createFromDate(2016, 2, 19)->startOfDay(),
        ];

        $center           = new Center();
        $center->id       = 0;
        $center->timezone = 'America/Chicago';

        return [
            // Use Defaults
            [
                null,
                $reportingDate = Carbon::createFromDate(2016, 2, 5)->startOfDay(),
                $quarterDates,
                $center,
                [
                    'report'   => null,
                    'response' => null,
                ],
            ],
            // Partial Report - classroom override
            [
                [
                    [
                        'reportingDate' => 'classroom3Date',
                        'report'        => [
                            'time' => '23:59:59',
                        ],
                    ],
                ],
                $reportingDate1 = $quarterDates['classroom3Date']->copy(),
                $quarterDates,
                $center,
                [
                    'report'   => Carbon::parse($reportingDate1->toDateString(), $center->timezone)
                                        ->setTime(23, 59, 59),
                    'response' => null,
                ],
            ],
            // Partial Report - week override
            [
                [
                    [
                        'reportingDate' => 'week1',
                        'report'        => [
                            'time' => '23:59:59',
                        ],
                        'response'      => [
                            'time' => '12:00:00',
                        ],
                    ],
                ],
                $reportingDate2 = $quarterDates['startWeekendDate']->copy()->addWeek(),
                $quarterDates,
                $center,
                [
                    'report'   => Carbon::parse($reportingDate2->toDateString(), $center->timezone)
                                        ->setTime(23, 59, 59),
                    'response' => Carbon::parse($reportingDate2->toDateString(), $center->timezone)
                                        ->addDay()
                                        ->setTime(12, 0, 0),
                ],
            ],
            // Partial Report - date override
            [
                [
                    [
                        'reportingDate' => '2015-12-25',
                        'report'        => [
                            'dueDate' => '2015-12-23',
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'dueDate' => '2015-12-23',
                            'time'    => '21:00:00',
                        ],
                    ],
                ],
                $reportingDate3 = Carbon::createFromDate(2015, 12, 25)->startOfDay(),
                $quarterDates,
                $center,
                [
                    'report'   => Carbon::create(2015, 12, 23, 17, 0, 59, $center->timezone),
                    'response' => Carbon::create(2015, 12, 23, 21, 0, 0, $center->timezone),
                ],
            ],
            // Full Report - endWeekendDate w/ timezone
            [
                [
                    [
                        'reportingDate' => 'endWeekendDate',
                        'report'        => [
                            'time'     => '17:00:00',
                            'timezone' => 'America/Los_Angeles',
                        ],
                        'response'      => [
                            'dueDate' => '+0days',
                            'time'    => '17:00:00',
                            'timezone' => 'America/Los_Angeles',
                        ],
                    ],
                ],
                $reportingDate4 = $quarterDates['endWeekendDate']->copy(),
                $quarterDates,
                $center,
                [
                    'report'   => Carbon::parse($reportingDate4->toDateString(), 'America/Los_Angeles')
                                        ->setTime(17, 0, 0),
                    'response' => Carbon::parse($reportingDate4->toDateString(), 'America/Los_Angeles')
                                        ->setTime(17, 0, 0),
                ],
            ],
            // Returns the correct date out of a series of overrides
            [
                [
                    [
                        'reportingDate' => 'week1',
                        'report'        => [
                            'time' => '23:59:59',
                        ],
                        'response'      => [
                            'time' => '12:00:00',
                        ],
                    ],
                    [
                        'reportingDate' => '2015-12-25',
                        'report'        => [
                            'dueDate' => '2015-12-23',
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'dueDate' => '2015-12-23',
                            'time'    => '21:00:00',
                        ],
                    ],
                    [
                        'reportingDate' => 'classroom3Date',
                        'report'        => [
                            'time' => '23:59:59',
                        ],
                    ],
                    [
                        'reportingDate' => 'endWeekendDate',
                        'report'        => [
                            'time'     => '17:00:00',
                            'timezone' => 'America/Los_Angeles',
                        ],
                        'response'      => [
                            'dueDate' => '+0days',
                            'time'    => '17:00:00',
                            'timezone' => 'America/Los_Angeles',
                        ],
                    ],
                ],
                $reportingDate3 = Carbon::createFromDate(2015, 12, 25)->startOfDay(),
                $quarterDates,
                $center,
                [
                    'report'   => Carbon::create(2015, 12, 23, 17, 0, 59, $center->timezone),
                    'response' => Carbon::create(2015, 12, 23, 21, 0, 0, $center->timezone),
                ],
            ],
        ];
    }

    /**
     * @dataProvider providerGetThrowsException
     */
    public function testGetThrowsException($setting, $reportingDate, $quarterDates, $center, $expectedException)
    {
        $this->setExpectedException($expectedException);

        $this->setSetting('reportDeadlines', $setting);

        $quarter = $this->getQuarterMock([], $quarterDates);

        ReportDeadlines::get($center, $quarter, $reportingDate);
    }

    public function providerGetThrowsException()
    {
        $quarterDates = [
            'startWeekendDate' => Carbon::createFromDate(2015, 11, 20)->startOfDay(),
            'classroom1Date'   => Carbon::createFromDate(2015, 12, 4)->startOfDay(),
            'classroom2Date'   => Carbon::createFromDate(2016, 1, 8)->startOfDay(),
            'classroom3Date'   => Carbon::createFromDate(2016, 2, 5)->startOfDay(),
            'endWeekendDate'   => Carbon::createFromDate(2016, 2, 19)->startOfDay(),
        ];

        $center           = new Center();
        $center->id       = 0;
        $center->timezone = 'America/Chicago';

        $reportingDate = Carbon::createFromDate(2016, 2, 5)->startOfDay();

        return [
            // Missing reportingDate
            [
                [
                    [
                        'report'        => [
                            'dueDate' => '2015-12-23',
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'dueDate' => '2015-12-23',
                            'time'    => '21:00:00',
                        ],
                    ],
                ],
                $reportingDate,
                $quarterDates,
                $center,
                '\Exception',
            ],
            // Invalid reportingDate
            [
                [
                    [
                        'reportingDate' => 'asdf',
                        'report'        => [
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'time'    => '21:00:00',
                        ],
                    ],
                ],
                $reportingDate,
                $quarterDates,
                $center,
                '\Exception',
            ],
            // Invalid report dueDate
            [
                [
                    [
                        'reportingDate' => 'classroom3Date',
                        'report'        => [
                            'dueDate' => 'asdf',
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'time'    => '21:00:00',
                        ],
                    ],
                ],
                $reportingDate,
                $quarterDates,
                $center,
                '\Exception',
            ],
            // Invalid report time
            [
                [
                    [
                        'reportingDate' => 'classroom3Date',
                        'report'        => [
                            'time'    => 'asdf',
                        ],
                        'response'      => [
                            'time'    => '21:00:00',
                        ],
                    ],
                ],
                $reportingDate,
                $quarterDates,
                $center,
                '\Exception',
            ],
            // Invalid response dueDate
            [
                [
                    [
                        'reportingDate' => 'classroom3Date',
                        'report'        => [
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'dueDate' => 'asdf',
                            'time'    => '21:00:00',
                        ],
                    ],
                ],
                $reportingDate,
                $quarterDates,
                $center,
                '\Exception',
            ],
            // Invalid response time
            [
                [
                    [
                        'reportingDate' => 'classroom3Date',
                        'report'        => [
                            'time'    => '17:00:59',
                        ],
                        'response'      => [
                            'time'    => 'asdf',
                        ],
                    ],
                ],
                $reportingDate,
                $quarterDates,
                $center,
                '\Exception',
            ],
        ];
    }

    protected function getStatsReportMock($methods = [], $constructorArgs = [])
    {
        $defaultMethods = ['__get'];
        $methods        = $this->mergeMockMethods($defaultMethods, $methods);

        $statsReport = $this->getMockBuilder(StatsReport::class)
                            ->setMethods($methods)
                            ->getMock();

        $statsReport->expects($this->any())
                    ->method('__get')
                    ->will($this->returnCallback(function ($name) use ($constructorArgs) {
                        return isset($constructorArgs[$name])
                            ? $constructorArgs[$name]
                            : null;
                    }));

        return $statsReport;
    }
}
