<?php
namespace TmlpStats\Domain;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use TmlpStats as Models;

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

    const MARKER_DATE = '1900-01-01';

    const SIMPLE_DATE_FIELDS = [
        'startWeekendDate',
        'endWeekendDate',
        'classroom1Date',
        'classroom2Date',
        'classroom3Date',
    ];

    const QUARTER_COPY_FIELDS = ['t1Distinction', 'year'];

    protected function __construct($center, $quarter)
    {
        $this->center = $center;
        $this->quarter = $quarter;
    }

    public static function fromModel(Models\Center $center, Models\Quarter $quarter)
    {
        $quarter->setRegion($center->region);

        $cq = new static($center, $quarter);
        foreach (static::SIMPLE_DATE_FIELDS as $field) {
            $cq->$field = $quarter->getQuarterDate($field, $center);
        }
        $cq->firstWeekDate = $cq->startWeekendDate->copy()->addWeek();

        return $cq;
    }

    private function formatDate($d)
    {
        $marker = Carbon::parse(static::MARKER_DATE); // XXX optimize this later

        if ($d === null || $d->lt($marker)) {
            return null;
        }

        return $d->toDateString();
    }

    public function toArray()
    {
        $v = [
            'quarterId' => $this->quarter->id,
            'centerId' => $this->center->id,
            'firstWeekDate' => $this->formatDate($this->firstWeekDate),
            'quarter' => [],
        ];
        foreach (static::SIMPLE_DATE_FIELDS as $field) {
            $v[$field] = $this->formatDate($this->$field);
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

}
