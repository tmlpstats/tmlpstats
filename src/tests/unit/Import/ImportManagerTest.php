<?php
namespace TmlpStats\Tests;

use Carbon\Carbon;
use stdClass;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Person;
use TmlpStats\Tests\Traits\MocksQuarters;
use TmlpStats\Tests\Traits\MocksSettings;

class ImportManagerTest extends TestAbstract
{
    use MocksSettings, MocksQuarters;

    protected $testClass = ImportManager::class;

    /**
     * @dataProvider providerGetEmail
     */
    public function testGetEmail($person, $expectedResult)
    {
        $result = ImportManager::getEmail($person);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerGetEmail()
    {
        $data = [];

        // No person provided, should return null
        $data[] = [null, null];

        // Person is unsubscribed, should return null
        $person = new Person([
            'email'        => 'test@tmlpstats.com',
            'unsubscribed' => true,
        ]);
        $data[] = [$person, null];

        // Person is not unsubscribed, should return email
        $person               = clone $person;
        $person->unsubscribed = false;
        $data[]               = [$person, 'test@tmlpstats.com'];

        return $data;
    }

    /**
     * @dataProvider providerGetStatsDueDateTime
     */
    public function testGetStatsDueDateTime($statsReport, $reportingDate, $settingData, $expectedResponse)
    {
        $this->markTestSkipped('Needs to be moved to StatsReport');

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

        $statsReport->quarter          = $this->getQuarterMock([], [
            'startWeekendDate' => Carbon::create(2015, 11, 20)->startOfDay(),
            'classroom1Date'   => Carbon::create(2015, 12, 4)->startOfDay(),
            'classroom2Date'   => Carbon::create(2016, 1, 8)->startOfDay(),
            'classroom3Date'   => Carbon::create(2016, 2, 5)->startOfDay(),
            'endWeekendDate'   => Carbon::create(2016, 2, 19)->startOfDay(),
        ]);

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
                $statsReport->quarter->getClassroom1Date(),
                $settings,
                Carbon::parse($statsReport->quarter->getClassroom1Date()->toDateString() . " 7:00:59pm", $timezone),
            ],
            // Report classroom2 override
            [
                $statsReport,
                $statsReport->quarter->getClassroom2Date(),
                $settings,
                Carbon::parse($statsReport->quarter->getClassroom2Date()->toDateString() . " 11:59:59pm", $timezone),
            ],
            // Report classroom3 override
            [
                $statsReport,
                $statsReport->quarter->getClassroom3Date(),
                $settings,
                Carbon::parse($statsReport->quarter->getClassroom3Date()->toDateString() . " 7:00:59pm", $timezone),
            ],
            // Report weekend completion override
            [
                $statsReport,
                $statsReport->quarter->getQuarterEndDate(),
                $settings,
                Carbon::parse($statsReport->quarter->getQuarterEndDate()->toDateString() . " 5:00:59pm", 'America/Chicago'),
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

        $statsReport->quarter          = $this->getQuarterMock([], [
            'startWeekendDate' => Carbon::create(2015, 11, 20)->startOfDay(),
            'classroom1Date'   => Carbon::create(2015, 12, 4)->startOfDay(),
            'classroom2Date'   => Carbon::create(2016, 1, 8)->startOfDay(),
            'classroom3Date'   => Carbon::create(2016, 2, 5)->startOfDay(),
            'endWeekendDate'   => Carbon::create(2016, 2, 19)->startOfDay(),
        ]);

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
                Carbon::create(2015, 11, 28, 10, 0, 0, $timezone),
            ],
            // Report classroom1 override next day
            [
                $statsReport,
                $statsReport->quarter->getClassroom1Date(),
                $settings,
                Carbon::create(2015, 12, 5, 10, 0, 0, $timezone),
            ],
            // Report classroom2 override next day
            [
                $statsReport,
                $statsReport->quarter->getClassroom2Date(),
                $settings,
                Carbon::create(2016, 1, 9, 12, 0, 0, $timezone),
            ],
            // Report classroom3 override same day
            [
                $statsReport,
                $statsReport->quarter->getClassroom3Date(),
                $settings,
                Carbon::create(2016, 2, 5, 21, 0, 0, $timezone),
            ],
            // Report specific reportingDate specific response date
            [
                $statsReport,
                Carbon::create(2016, 1, 1)->startOfDay(),
                $settings,
                Carbon::create(2015, 12, 31, 21, 0, 0, $timezone),
            ],
        ];
    }
}
