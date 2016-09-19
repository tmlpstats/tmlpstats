<?php
namespace TmlpStats\Domain;

use App;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain\Logic\QuarterDates;
use TmlpStats\Encapsulations\RegionQuarter;
use TmlpStats\Settings\RegionQuarterOverride;
use TmlpStats\Settings\Setting;

/**
 * Represents a quarter for a specific center.
 *
 * This is currently not a parser domain because there's not much use in fromArray / input
 * validation. That may change in the future if we support modifying quarter details via the API
 * (like for regional statistician uses)
 */
class CenterQuarter implements Arrayable, \JsonSerializable
{
    public $center = null;
    public $quarter = null;
    public $firstWeekDate = null;

    public $startWeekendDate = null;
    public $endWeekendDate = null;
    public $classroom1Date = null;
    public $classroom2Date = null;
    public $classroom3Date = null;
    protected $repromiseDate = null;

    const QUARTER_COPY_FIELDS = ['t1Distinction', 'year', 'id'];

    public function __construct(Models\Center $center, Models\Quarter $quarter)
    {
        $this->center = $center;
        $this->quarter = $quarter;

        // TODO consider removing this.
        $quarter->setRegion($center->region);

        // TODO consider moving RegionQuarterOverride logic into this class
        $overriddenDates = RegionQuarterOverride::get($center, $quarter);
        $regionQuarter = RegionQuarter::ensure($center->region, $quarter);

        foreach (QuarterDates::SIMPLE_DATE_FIELDS as $field) {
            $d = isset($overriddenDates[$field]) ? QuarterDates::fixDateInput($overriddenDates[$field]) : null;
            if ($d === null) {
                $d = QuarterDates::fixDateInput($regionQuarter->$field);
            }
            $this->$field = $d;
        }
        $this->firstWeekDate = $this->startWeekendDate->copy()->addWeek();
    }

    public static function ensure(Models\Center $center, Models\Quarter $quarter)
    {
        return App::make(Api\Context::class)->getEncapsulation(self::class, compact('center', 'quarter'));
    }

    public static function fromModel(Models\Center $center, Models\Quarter $quarter)
    {
        return new static($center, $quarter);
    }

    public function toArray()
    {
        $v = [
            'quarterId' => $this->quarter->id,
            'centerId' => $this->center->id,
            'firstWeekDate' => QuarterDates::formatDate($this->firstWeekDate),
            'quarter' => [],
        ];
        foreach (QuarterDates::SIMPLE_DATE_FIELDS as $field) {
            $v[$field] = QuarterDates::formatDate($this->$field);
        }

        // Yes, we copy these fields, but it's just easier than having to deal with quarters too in JSON
        foreach (static::QUARTER_COPY_FIELDS as $field) {
            $v['quarter'][$field] = $this->quarter->$field;
        }

        return $v;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * List all reporting dates in a center quarter
     * @param  Center|null $center The center
     * @return array               An array of Carbon objects being each reporting week in the quarter
     */
    public function listReportingDates()
    {
        $output = [];
        $d = $this->$startWeekendDate->copy();
        while ($d->lt($endDate)) {
            $output[] = $d;
            $d = $d->copy()->addWeek();
        }

        return $output;
    }

    /**
     * Get the date when repromises are accepted
     *
     * Will check for a setting override, otherwise uses the classroom2 date
     *
     * @return Carbon
     */
    public function getRepromiseDate()
    {
        if ($this->repromiseDate === null) {
            $setting = Setting::name('repromiseDate')
                ->with($this->center, $this->quarter)
                ->get();
            $this->repromiseDate = $setting ?: $this->classroom2Date;
        }

        return $this->repromiseDate;
    }

        /**
     * Is provided date the week to accept repromises?
     *
     * Will check for a setting override, otherwise uses the classroom2 date
     *
     * @param Carbon  $date    A reportingDate that is a day centered at midnight
     * @param Center  $center  The center.
     *
     * @return bool
     */
    public function isRepromiseWeek(Carbon $date)
    {
        $repromiseDate = $this->getRepromiseDate();

        return $date->eq($repromiseDate);
    }

}
