<?php
namespace TmlpStats\Tests;

use Carbon\Carbon;
use stdClass;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Tests\Traits\MocksSettings;

class ImportManagerTest extends TestAbstract
{
    use MocksSettings;

    protected $testClass = ImportManager::class;

    /**
     * @dataProvider providerGetStatsDueDateTime
     */
    public function testGetStatsDueDateTime($statsReport, $reportingDate, $settingData, $expectedResponse)
    {
        $this->unsetSetting('centerReportDue');
        if ($settingData !== null) {
            $this->setSetting('centerReportDue', json_encode($settingData));
        }

        $statsReport->reportingDate = $reportingDate;

        $result = ImportManager::getStatsDueDateTime($statsReport);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($expectedResponse->eq($result));
    }

    public function providerGetStatsDueDateTime()
    {
        $timezone = 'America/Vancouver';

        $statsReport                   = new stdClass;
        $statsReport->center           = new Center();
        $statsReport->center->id       = 0;
        $statsReport->center->timezone = $timezone;

        $statsReport->quarter                   = new stdClass;
        $statsReport->quarter->startWeekendDate = Carbon::create(2015, 11, 20)->startOfDay();
        $statsReport->quarter->classroom1Date   = Carbon::create(2015, 12, 4)->startOfDay();
        $statsReport->quarter->classroom2Date   = Carbon::create(2016, 1, 8)->startOfDay();
        $statsReport->quarter->classroom3Date   = Carbon::create(2016, 2, 5)->startOfDay();
        $statsReport->quarter->endWeekendDate   = Carbon::create(2016, 2, 19)->startOfDay();

        $settings = [
            [
                'reportingDate' => 'week1',
                'time'          => '8:00:59pm',
            ],
            [
                'reportingDate' => 'classroom1Date',
                'time'          => '7:00:59pm',
            ],
            [
                'reportingDate' => 'classroom2Date',
                'time'          => '11:59:59pm',
            ],
            [
                'reportingDate' => 'classroom3Date',
                'time'          => '7:00:59pm',
            ],
            [
                'reportingDate' => 'endWeekendDate',
                'time'          => '5:00:59pm',
                'timezone'      => 'America/Chicago',
            ],
            [
                'reportingDate' => '2016-01-01',
                'dueDate'       => '2015-12-31',
                'time'          => '5:00:59pm',
            ],
        ];

        return [
            // Report standard week with no override
            [
                $statsReport,
                Carbon::create(2015, 11, 27)->startOfDay(),
                [],
                Carbon::create(2015, 11, 27, 19, 0, 59, $timezone),
            ],
            // Report week1 override
            [
                $statsReport,
                Carbon::create(2015, 11, 27)->startOfDay(),
                $settings,
                Carbon::create(2015, 11, 27, 20, 0, 59, $timezone),
            ],
            // Report classroom1 override
            [
                $statsReport,
                $statsReport->quarter->classroom1Date,
                $settings,
                Carbon::parse($statsReport->quarter->classroom1Date->toDateString() . " 7:00:59pm", $timezone),
            ],
            // Report classroom2 override
            [
                $statsReport,
                $statsReport->quarter->classroom2Date,
                $settings,
                Carbon::parse($statsReport->quarter->classroom2Date->toDateString() . " 11:59:59pm", $timezone),
            ],
            // Report classroom3 override
            [
                $statsReport,
                $statsReport->quarter->classroom3Date,
                $settings,
                Carbon::parse($statsReport->quarter->classroom3Date->toDateString() . " 7:00:59pm", $timezone),
            ],
            // Report weekend completion override
            [
                $statsReport,
                $statsReport->quarter->endWeekendDate,
                $settings,
                Carbon::parse($statsReport->quarter->endWeekendDate->toDateString() . " 5:00:59pm", 'America/Chicago'),
            ],
            // Report specific reportingDate override
            [
                $statsReport,
                Carbon::create(2016, 1, 1)->startOfDay(),
                $settings,
                Carbon::create(2015, 12, 31, 17, 0, 59, $timezone),
            ],
        ];
    }

    /**
     * @dataProvider providerGetRegionalRespondByDateTime
     */
    public function testGetRegionalRespondByDateTime($statsReport, $reportingDate, $settingData, $expectedResponse)
    {
        $this->unsetSetting('centerReportRespondByTime');
        if ($settingData !== null) {
            $this->setSetting('centerReportRespondByTime', json_encode($settingData));
        }

        $statsReport->reportingDate = $reportingDate;

        $result = ImportManager::getRegionalRespondByDateTime($statsReport);

        $this->assertInstanceOf(Carbon::class, $result);
        $this->assertTrue($expectedResponse->eq($result));
    }

    public function providerGetRegionalRespondByDateTime()
    {
        $timezone = 'America/Vancouver';

        $statsReport                   = new stdClass;
        $statsReport->center           = new Center();
        $statsReport->center->id       = 0;
        $statsReport->center->timezone = $timezone;

        $statsReport->quarter                   = new stdClass;
        $statsReport->quarter->startWeekendDate = Carbon::create(2015, 11, 20)->startOfDay();
        $statsReport->quarter->classroom1Date   = Carbon::create(2015, 12, 4)->startOfDay();
        $statsReport->quarter->classroom2Date   = Carbon::create(2016, 1, 8)->startOfDay();
        $statsReport->quarter->classroom3Date   = Carbon::create(2016, 2, 5)->startOfDay();
        $statsReport->quarter->endWeekendDate   = Carbon::create(2016, 2, 19)->startOfDay();

        $settings = [
            [
                'reportingDate' => 'week1',
                'dueDate'       => '+1day',
                'time'          => '12:00:00pm',
            ],
            [
                'reportingDate' => 'classroom1Date',
                'dueDate'       => '+1day',
                'time'          => '10:00:00am',
            ],
            [
                'reportingDate' => 'classroom2Date',
                'dueDate'       => '+1day',
                'time'          => '12:00:00pm',
            ],
            [
                'reportingDate' => 'classroom3Date',
                'time'          => '9:00:00pm',
            ],
            [
                'reportingDate' => '2016-01-01',
                'dueDate'       => '2015-12-31',
                'time'          => '9:00:00pm',
            ],
        ];

        return [
            // Report standard week with no override
            [
                $statsReport,
                Carbon::create(2015, 11, 27)->startOfDay(),
                [],
                Carbon::create(2015, 11, 28, 10, 0, 59, $timezone),
            ],
            // Report classroom1 override next day
            [
                $statsReport,
                $statsReport->quarter->classroom1Date,
                $settings,
                Carbon::parse($statsReport->quarter->classroom1Date->copy()->addDay()->toDateString() . " 10:00:00am", $timezone),
            ],
            // Report classroom2 override next day
            [
                $statsReport,
                $statsReport->quarter->classroom2Date,
                $settings,
                Carbon::parse($statsReport->quarter->classroom2Date->copy()->addDay()->toDateString() . " 12:00:00pm", $timezone),
            ],
            // Report classroom3 override same day
            [
                $statsReport,
                $statsReport->quarter->classroom3Date,
                $settings,
                Carbon::parse($statsReport->quarter->classroom3Date->toDateString() . " 9:00:00pm", $timezone),
            ],
            // Report specific reportingDate specific response date
            [
                $statsReport,
                Carbon::create(2016, 1, 1)->startOfDay(),
                $settings,
                Carbon::create(2015, 12, 31, 21, 0, 00, $timezone),
            ],
        ];
    }
}
