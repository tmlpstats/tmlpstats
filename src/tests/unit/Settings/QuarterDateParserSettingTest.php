<?php
namespace TmlpStats\Tests\Settings;

use Carbon\Carbon;
use TmlpStats\Center;
use TmlpStats\Settings\Parsers\QuarterDateParser;
use TmlpStats\Settings\Setting;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Traits\MocksQuarters;
use TmlpStats\Tests\Traits\MocksSettings;

class QuarterDateParserSettingTest extends TestAbstract
{
    use MocksSettings, MocksQuarters;

    protected $testClass = Setting::class;

    public function tearDown()
    {
        parent::tearDown();

        $this->clearSettings();
    }

    /**
     * @dataProvider providerGet
     */
    public function testGet($settingName, $settingValue, $parserClass, $quarterDates, $expectedResponse)
    {
        $this->setSetting($settingName, $settingValue);

        $quarter = $this->getQuarterMock([], $quarterDates);

        $center     = new Center();
        $center->id = 0;

        $builder = Setting::name($settingName)
                          ->with($center, $quarter);

        if ($parserClass) {
            $builder->parserClass($parserClass);
        }

        $result = $builder->get();

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

        return [
            // Test using custom setting with quarter dates
            [
                'mySetting',
                'classroom1Date',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['classroom1Date'],
            ],
            [
                'mySetting',
                'classroom2Date',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['classroom2Date'],
            ],
            [
                'mySetting',
                'classroom3Date',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['classroom3Date'],
            ],
            [
                'mySetting',
                'endWeekendDate',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['endWeekendDate'],
            ],
            // Test using known setting with quarter dates
            [
                'repromiseDate',
                'classroom1Date',
                null,
                $quarterDates,
                $quarterDates['classroom1Date'],
            ],
            [
                'travelDueByDate',
                'classroom3Date',
                null,
                $quarterDates,
                $quarterDates['classroom3Date'],
            ],
            // Test using custom setting with week offsets
            [
                'mySetting',
                'week1',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeek(),
            ],
            [
                'mySetting',
                'week2',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeeks(2),
            ],
            [
                'mySetting',
                'week4',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeeks(4),
            ],
            [
                'mySetting',
                'week6',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeeks(6),
            ],
            [
                'mySetting',
                'week8',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeeks(8),
            ],
            [
                'mySetting',
                'week10',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeeks(10),
            ],
            [
                'mySetting',
                'week12',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['startWeekendDate']->copy()->addWeeks(12),
            ],
            // Test using custom setting with explicit date
            [
                'mySetting',
                '2016-02-06',
                QuarterDateParser::class,
                $quarterDates,
                Carbon::create(2016, 2, 6)->startOfDay(),
            ],
            // Test empty setting returns default
            [
                'mySetting',
                '',
                QuarterDateParser::class,
                $quarterDates,
                $quarterDates['classroom2Date'],
            ],
        ];
    }
}
