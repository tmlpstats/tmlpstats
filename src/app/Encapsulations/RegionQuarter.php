<?php
namespace TmlpStats\Encapsulations;

use App;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain\Logic\QuarterDates;

/**
 * RegionQuarter encapsulates data specific to quarters in a region.
 *
 * Use it through the Context class or via the `::ensure` method
 * to allow the context to manage the instantiation and caching of these details.
 */
class RegionQuarter
{
    protected $region;
    protected $quarter;

    private $dates = [];

    public function __construct(Models\Region $region, Models\Quarter $quarter)
    {
        $this->region = $region;
        $this->quarter = $quarter;

        $rqd = Models\RegionQuarterDetails::byQuarter($quarter)
            ->byRegion($region)
            ->first();

        foreach (QuarterDates::SIMPLE_DATE_FIELDS as $field) {
            $this->dates[$field] = QuarterDates::fixDateInput($rqd->$field);
        }
        $this->dates['firstWeekDate'] = $this->dates['startWeekendDate']->copy()->addWeek();
    }

    public static function ensure(Models\Region $region, Models\Quarter $quarter)
    {
        return App::make(Api\Context::class)->getEncapsulation(self::class, compact('region', 'quarter'));
    }

    public function __get($name)
    {
        return $this->dates[$name];
    }

    public function datesAsArray()
    {
        return $this->dates;
    }
}
