<?php
namespace TmlpStats\Encapsulations;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain\Logic\QuarterDates;

/**
 * RegionQuarter encapsulates data specific to quarters in a region.
 *
 * Use it through the Context class or via the `::ensure` method
 * to allow the context to manage the instantiation and caching of these details.
 */
class RegionQuarter implements \JsonSerializable
{
    protected $region;
    protected $quarter;

    private $dates = [];

    public function __construct(Models\Region $region, Models\Quarter $quarter, Models\RegionQuarterDetails $rqd = null)
    {
        $this->region = $region;
        $this->quarter = $quarter;

        if ($rqd === null || !$rqd->quarterId) {
            $rqd = Models\RegionQuarterDetails::byQuarter($quarter)
                ->byRegion($region)
                ->first();
        }

        if (!$rqd) {
            throw new \Exception("Quarter information is not setup completely for {$region->name} region during the {$quarter->t1Distinction} quarter {$quarter->year}.");
        }

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

    /**
     * List all reporting dates in a region quarter
     *
     * @return array An array of Carbon objects being each reporting week in the quarter
     */
    public function listReportingDates()
    {
        $output = [];
        $d = $this->dates['firstWeekDate']->copy();
        while ($d->lte($this->dates['endWeekendDate'])) {
            $output[] = $d;
            $d = $d->copy()->addWeek();
        }

        return $output;
    }

    public function getNextMilestone(Carbon $ref = null): Carbon
    {
        return QuarterDates::getNextMilestone($this, $ref);
    }

    /**
     * Get the date by which travel/room information is due.
     *
     * Will check for setting override, otherwise uses classroom 3 date
     *
     * @return Carbon
     */
    public function getTravelDueByDate()
    {
        $setting = $this->getSetting('travelDueByDate');

        return ($setting) ? QuarterDates::parseQuarterDate($setting, $this) : $this->classroom2Date;
    }

    private function getSetting($name) 
    {
        return App::make(Api\Context::class)->getSetting($name, $this->region, $this->quarter);
    }

    public function toArray()
    {
        $v = [
            'id' => "{$this->region->id}/{$this->quarter->id}",
            'regionId' => $this->region->id,
            'quarterId' => $this->quarter->id,
        ];
        foreach ($this->dates as $k => $d) {
            $v[$k] = QuarterDates::formatDate($d);
        }

        return $v;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
