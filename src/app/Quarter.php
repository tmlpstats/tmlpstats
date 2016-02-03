<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class Quarter extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        't1_distinction',
        't2_distinction',
        'quarter_number',
        'year',
    );

    protected $region = null;
    protected $regionQuarterDetails = null;

    public function __get($name)
    {
        switch ($name) {
            case 'startWeekendDate':
            case 'endWeekendDate':
            case 'classroom1Date':
            case 'classroom2Date':
            case 'classroom3Date':
                if ($this->region && !$this->regionQuarterDetails) {
                    $this->setRegion($this->region);
                }
                if (!$this->regionQuarterDetails) {
                    throw new Exception("Cannot call __get({$name}) before setting region.");
                }
                return $this->getQuarterDate($name);
            default:
                return parent::__get($name);
        }
    }

    /**
     * Get the date of the first week of reporting
     *
     * @param Center|null $center
     *
     * @return Carbon
     */
    public function getFirstWeekDate(Center $center = null)
    {
        $quarterStart = $this->getQuarterStartDate($center);

        return $quarterStart->addWeek();
    }

    /**
     * Get the Friday date of the quarter's starting weekend
     *
     * @param Center|null $center
     *
     * @return Carbon
     * @throws Exception
     */
    public function getQuarterStartDate(Center $center = null)
    {
        return $this->getQuarterDate('startWeekendDate', $center);
    }

    /**
     * Get the Friday date of the quarter's ending weekend
     *
     * @param Center|null $center
     *
     * @return Carbon
     * @throws Exception
     */
    public function getQuarterEndDate(Center $center = null)
    {
        return $this->getQuarterDate('endWeekendDate', $center);
    }

    /**
     * Get the date of the quarter's classroom 1
     *
     * @param Center|null $center
     *
     * @return Carbon
     * @throws Exception
     */
    public function getClassroom1Date(Center $center = null)
    {
        return $this->getQuarterDate('classroom1Date', $center);
    }

    /**
     * Get the date of the quarter's classroom 2
     *
     * @param Center|null $center
     *
     * @return Carbon
     * @throws Exception
     */
    public function getClassroom2Date(Center $center = null)
    {
        return $this->getQuarterDate('classroom2Date', $center);
    }

    /**
     * Get the date of the quarter's classroom 3
     *
     * @param Center|null $center
     *
     * @return Carbon
     * @throws Exception
     */
    public function getClassroom3Date(Center $center = null)
    {
        return $this->getQuarterDate('classroom3Date', $center);
    }

    /**
     * Get the date for the $field value.
     *
     * Will check if there is a setting overriding the regional date, or return the regional date
     *
     * @param             $field
     * @param Center|null $center
     *
     * @return Carbon
     * @throws Exception
     */
    public function getQuarterDate($field, Center $center = null)
    {
        $validFields = [
            'startWeekendDate',
            'endWeekendDate',
            'classroom1Date',
            'classroom2Date',
            'classroom3Date',
        ];

        if (!in_array($field, $validFields)) {
            throw new \Exception("{$field} is not a valid date field.");
        }

        if (!$this->regionQuarterDetails) {
            throw new \Exception("regionQuarterDetails not set. Cannot determine {$field}.");
        }

        $date = $this->regionQuarterDetails->$field;

        $settings = Setting::get('regionQuarterOverride', $center, $this);
        if ($settings) {
            // Settings should be in the format:
            // {"classroom2Date":"2016-01-15", "classroom3Date":"2016-02-07"}
            $dateSettings = $settings->value
                ? json_decode($settings->value, true)
                : [];

            if (isset($dateSettings[$field])) {
                $date = Carbon::parse($dateSettings[$field]);
            }
        }

        return $date->startOfDay();
    }

    /**
     * Get the date when repromises are accepted
     *
     * Will check for a setting override, otherwise uses the classroom2 date
     *
     * @param Center|null $center
     *
     * @return Carbon|static
     */
    public function getRepromiseDate(Center $center = null)
    {
        $setting = Setting::get('repromiseDate', $center, $this);
        if ($setting) {
            return Carbon::parse($setting->value);
        }

        return $this->getClassroom2Date($center);
    }

    /**
     * Is provided date the week to accept repromises?
     *
     * Will check for a setting override, otherwise uses the classroom2 date
     *
     * @param Carbon      $date
     * @param Center|null $center
     *
     * @return bool
     */
    public function isRepromiseWeek(Carbon $date, Center $center = null)
    {
        $repromiseDate = $this->getRepromiseDate($center);

        return $date->eq($repromiseDate);
    }

    public static function isFirstWeek(Region $region)
    {
        $reportingDate = Util::getReportDate();
        $quarter = Quarter::getQuarterByDate($reportingDate, $region);

        if ($quarter) {
            $firstWeek = $quarter->getFirstWeekDate();
            return $firstWeek->eq($reportingDate);
        }
        return false;
    }

    public static function getCurrentQuarter(Region $region)
    {
        $date = Util::getReportDate();
        return static::getQuarterByDate($date, $region);
    }


    public static function findForCenter($id, Center $center)
    {
        $key = "quarter:region{$center->regionId}";
        return static::getFromCache($key, $id, function() use ($id, $center) {
            $quarter = Quarter::find($id);
            if ($quarter) {
                $quarter->setRegion($center->region);
            }
            return $quarter;
        });
    }

    public static function getQuarterByDate(Carbon $date, Region $region)
    {
        $dateString = $date->toDateString();
        $key = "quarters:region{$region->id}:dates";
        return static::getFromCache($key, $dateString, function() use ($date, $region) {
            $quarter = Quarter::byRegion($region)
                ->date($date)
                ->first();

            if ($quarter) {
                $quarter->setRegion($region);
            }
            return $quarter;
        });
    }

    public function getNextQuarter()
    {
        $quarterNumber = ($this->quarterNumber + 1) % 5;
        $quarterNumber = $quarterNumber ?: 1; // no quarter 0

        $year = ($quarterNumber === 1)
            ? $this->year + 1
            : $this->year;

        $quarter = Quarter::year($year)->quarterNumber($quarterNumber)->first();

        if ($this->region) {
            $quarter->setRegion($this->region);
        }
        return $quarter;
    }

    public function setRegion($region)
    {
        if (!$region) {
            throw new Exception('Cannot set empty region.');
        }

        $this->region = $region;

        $this->regionQuarterDetails = RegionQuarterDetails::byQuarter($this)
            ->byRegion($this->region)
            ->first();
    }

    public function scopeQuarterNumber($query, $number)
    {
        return $query->whereQuarterNumber($number);
    }

    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeByRegion($query, Region $region)
    {
        $this->region = $region;

        // This may end up being redundant
        return $query->whereIn('id', function ($query) use ($region) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('region_id', $region->id)
                ->orWhere('region_id', $region->parentId);
        });
    }

    public function scopeDate($query, Carbon $date)
    {
        return $query->whereIn('id', function ($query) use ($date) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('start_weekend_date', '<', $date->startOfDay()->toDateString())
                ->where('end_weekend_date', '>=', $date->startOfDay()->toDateString());

            if ($this->region) {
                $query->where(function ($query) {
                    $query->where('region_id', $this->region->id)
                        ->orWhere('region_id', $this->region->parentId);
                });
            }
        });
    }

    public function scopeCurrent($query)
    {
        $date = Util::getReportDate();
        return $query->whereIn('id', function ($query) use ($date) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('start_weekend_date', '<', $date->startOfDay())
                ->where('end_weekend_date', '>=', $date->startOfDay());

            if ($this->region) {
                $query->where(function ($query) {
                    $query->where('region_id', $this->region->id)
                        ->orWhere('region_id', $this->region->parentId);
                });
            }
        });
    }

    public function scopePresentAndFuture($query)
    {
        $date = Util::getReportDate();
        return $query->whereIn('id', function ($query) use ($date) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('end_weekend_date', '>=', $date->startOfDay());

            if ($this->region) {
                $query->where(function ($query) {
                    $query->where('region_id', $this->region->id)
                        ->orWhere('region_id', $this->region->parentId);
                });
            }
        });
    }

    public function statsReport()
    {
        return $this->hasMany('TmlpStats\StatsReport');
    }
}
