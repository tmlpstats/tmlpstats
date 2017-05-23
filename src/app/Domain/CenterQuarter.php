<?php
namespace TmlpStats\Domain;

use App;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain\Logic\QuarterDates;
use TmlpStats\Encapsulations\RegionQuarter;
use TmlpStats\Settings\Setting;
use TmlpStats\Traits\ParsesQuarterDates;

/**
 * Represents a quarter for a specific center.
 *
 * This is currently not a parser domain because there's not much use in fromArray / input
 * validation. That may change in the future if we support modifying quarter details via the API
 * (like for regional statistician uses)
 */
class CenterQuarter implements Arrayable, \JsonSerializable
{
    use ParsesQuarterDates;

    public $center = null;
    public $quarter = null;
    public $firstWeekDate = null;

    public $startWeekendDate = null;
    public $endWeekendDate = null;
    public $classroom1Date = null;
    public $classroom2Date = null;
    public $classroom3Date = null;
    protected $repromiseDate = null;

    protected $context = null;

    const QUARTER_COPY_FIELDS = ['t1Distinction', 'year', 'id'];

    public function __construct(Models\Center $center, Models\Quarter $quarter, Api\Context $context = null)
    {
        $this->center = $center;
        $this->quarter = $quarter;
        $this->context = $context ?: App::make(Api\Context::class);

        $overriddenDates = $this->overriddenDates($center, $quarter);
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

    protected function overriddenDates($center, $quarter)
    {
        $dates = $this->getSetting('regionQuarterOverride');
        if ($dates === null) {
            return [];
        }

        foreach ($dates as $k => &$v) {
            $v = Carbon::parse($v);
        }

        return $dates;
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
        $d = $this->firstWeekDate->copy();
        while ($d->lte($this->endWeekendDate)) {
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
            $setting = $this->getSetting('repromiseDate');
            $this->repromiseDate = $setting ? Carbon::parse($setting) : $this->classroom2Date;
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

        return ($setting) ? $this->parseQuarterDate($setting) : $this->classroom2Date;
    }

    private function getSetting($name)
    {
        return $this->context->getSetting($name, $this->center, $this->quarter);
    }

    /**
     * Get a display string for this quarter.
     * @return string
     */
    public function displayString()
    {
        $startDate = $this->startWeekendDate->toDateString();

        return "{$this->quarter->t1Distinction} {$this->quarter->year} (begins {$startDate})";
    }

}
