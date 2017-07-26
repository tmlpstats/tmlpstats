<?php
namespace TmlpStats\Tests\Unit\Traits;

use App;
use TmlpStats as Models;
use Carbon\Carbon;
use TmlpStats\Domain\CenterQuarter;
use TmlpStats\Domain\Logic\QuarterDates;
use TmlpStats\Encapsulations\RegionQuarter;
use TmlpStats\Quarter;

trait MocksQuarters
{
    protected static $idOffset = 0;

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
            'getNextQuarter',
        ];
        $methods = $this->mergeMockMethods($defaultMethods, $methods);

        $quarter = $this->getMockBuilder(Quarter::class)
                        ->setMethods($methods)
                        ->getMock();

        static::$idOffset++;
        $quarter->id = static::$idOffset;

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

        $nextQuarter = isset($data['nextQuarter'])
            ? $data['nextQuarter']
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

        $quarter->method('getNextQuarter')
                ->willReturn($nextQuarter);

        $region = new Models\Region();
        $region->id = $quarter->id;
        $quarter->setRegion($region);
        SimpleRegionQuarter::$rqdLookup[$quarter->id] = $data;

        return $quarter;
    }

    protected function mockCenterQuarter()
    {
        App::bind(CenterQuarter::class, SimpleCenterQuarter::class);
    }

    protected function mockRegionQuarter($dates)
    {
        SimpleRegionQuarter::$cachedDates = $dates;
        App::bind(RegionQuarter::class, SimpleRegionQuarter::class);
    }

    protected function clearRegionQuarterMock() {
        SimpleRegionQuarter::$rqdLookup = [];
    }
}

class SimpleCenterQuarter extends CenterQuarter
{
    protected function overriddenDates($center, $quarter)
    {
        if (!$center->regionId) {
            $center->regionId = 123;
            $center->setRelation('region', new Models\Region(['id' => 123]));
        }

        return [];
    }
}

class SimpleRegionQuarter extends RegionQuarter
{
    public static $rqdLookup = [];
    public static $cachedDates = [];
    public function __construct(Models\Region $region, Models\Quarter $quarter, Models\RegionQuarterDetails $rqd = null)
    {

        if ($rqd === null || !$rqd->id) {
            $props = ['id' => 123, 'quarter_id' => $quarter->id, 'region_id' => $region->id];
            $data = array_get(static::$rqdLookup, $quarter->id, static::$cachedDates);
            //print_r($quarter->id);
            //print_r(static::$rqdLookup);
            $rqd = new Models\RegionQuarterDetails(array_merge($props, $data));
            foreach ($data as $k => $v) {
                $rqd->$k = $v;
            }
            if (!$rqd->startWeekendDate) {
                $rqd->startWeekendDate = Carbon::createFromDate(2015, 4, 3)->startOfDay();
            }
        }

        return parent::__construct($region, $quarter, $rqd);
    }
}
