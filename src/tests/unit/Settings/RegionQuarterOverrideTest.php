<?php
namespace TmlpStats\Tests\Settings;

use Carbon\Carbon;
use TmlpStats\Center;
use TmlpStats\Quarter;
use TmlpStats\Settings\RegionQuarterOverride;
use TmlpStats\StatsReport;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Traits\MocksSettings;

class RegionQuarterOverrideTest extends TestAbstract
{
    use MocksSettings;

    protected $testClass = StatsReport::class;


    public function tearDown()
    {
        parent::tearDown();

        $this->clearSettings();
    }

    /**
     * @dataProvider providerGet
     */
    public function testGet($setting, $center, $quarter, $expectedResponse)
    {
        $this->setSetting('regionQuarterOverride', $setting);

        $result = RegionQuarterOverride::get($center, $quarter);

        $this->assertEquals($expectedResponse, $result);
    }

    public function providerGet()
    {
        $quarter    = new Quarter();
        $center     = new Center();
        $center->id = 0;

        return [
            // Use Defaults
            [
                [],
                $center,
                $quarter,
                [],
            ],
            // Override all
            [
                [
                    'startWeekendDate' => '2015-11-20',
                    'classroom1Date'   => '2015-12-04',
                    'classroom2Date'   => '2016-01-08',
                    'classroom3Date'   => '2016-02-05',
                    'endWeekendDate'   => '2016-02-19',
                ],
                $center,
                $quarter,
                [
                    'startWeekendDate' => Carbon::createFromDate(2015, 11, 20)->startOfDay(),
                    'classroom1Date'   => Carbon::createFromDate(2015, 12, 4)->startOfDay(),
                    'classroom2Date'   => Carbon::createFromDate(2016, 1, 8)->startOfDay(),
                    'classroom3Date'   => Carbon::createFromDate(2016, 2, 5)->startOfDay(),
                    'endWeekendDate'   => Carbon::createFromDate(2016, 2, 19)->startOfDay(),
                ],
            ],
            // Override some
            [
                [
                    'classroom2Date'   => '2016-01-08',
                    'classroom3Date'   => '2016-02-05',
                ],
                $center,
                $quarter,
                [
                    'classroom2Date'   => Carbon::createFromDate(2016, 1, 8)->startOfDay(),
                    'classroom3Date'   => Carbon::createFromDate(2016, 2, 5)->startOfDay(),
                ],
            ],
        ];
    }

    /**
     * @expectedException \Exception
     */
    public function testGetThrowsExceptionWithInvalidFormat()
    {
        $quarter    = new Quarter();
        $center     = new Center();
        $center->id = 0;

        $this->setSetting('regionQuarterOverride', []);

        $method = new RegionQuarterOverride($center, $quarter);

        $this->setProperty($method, 'format', 'invalid');

        $method->getValue();
    }

    /**
     * Get a mock StatsReport object.
     *
     * @param array $methods  Specify methods to mock
     * @param array $data     Data dictionary used when returning values from overridden __get
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStatsReportMock($methods = [], $data = [])
    {
        $defaultMethods = ['__get'];
        $methods        = $this->mergeMockMethods($defaultMethods, $methods);

        $statsReport = $this->getMockBuilder(StatsReport::class)
                            ->setMethods($methods)
                            ->getMock();

        $statsReport->expects($this->any())
                    ->method('__get')
                    ->will($this->returnCallback(function ($name) use ($data) {
                        return isset($data[$name])
                            ? $data[$name]
                            : null;
                    }));

        return $statsReport;
    }
}
