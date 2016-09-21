<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Domain;
use TmlpStats\Settings\Setting;
use TmlpStats\Traits\CachedRelationships;
use TmlpStats\Traits\UsesContext;

class Quarter extends Model
{
    use CamelCaseModel, CachedRelationships, UsesContext;

    protected $fillable = [
        't1_distinction',
        't2_distinction',
        'quarter_number',
        'year',
    ];

    protected $region = null;
    protected $regionQuarterDetails = null;

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

        return $quarterStart->copy()->addWeek();
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

        if ($center === null) {
            // Use the old school region quarter concept if we get a null center. Hope to log and eliminate these very quickly.
            $data = $this->context()->getEncapsulation(Encapsulations\RegionQuarter::class, ['region' => $this->region, 'quarter' => $this]);
        } else {
            $data = $this->getCenterQuarter($center);
        }

        // To keep old behaviour, replace any missing dates with year 0001-01-01
        $date = $data->$field ?: Carbon::parse('0001-01-01', 'UTC');

        return $date->startOfDay();
    }

    /**
     * Get a CenterQuarter encapsulating dates for this center. Returns the same object from cache.
     * @param  Center $center The center for which we want a centerquarter.
     * @return Domain\CenterQuarter
     */
    public function getCenterQuarter(Center $center) {
        return $this->context()->getEncapsulation(Domain\CenterQuarter::class, ['center' => $center, 'quarter' => $this]);
    }

    public function getNextMilestone(Carbon $now)
    {
        if ($now->gt($this->getClassroom3Date())) {
            $nextMilestone = $this->getQuarterEndDate();
        } else if ($now->gt($this->getClassroom2Date())) {
            $nextMilestone = $this->getClassroom3Date();
        } else if ($now->gt($this->getClassroom1Date())) {
            $nextMilestone = $this->getClassroom2Date();
        } else {
            $nextMilestone = $this->getClassroom1Date();
        }

        return $nextMilestone;
    }

    /**
     * Get the date when repromises are accepted
     *
     * Will check for a setting override, otherwise uses the classroom2 date
     *
     * @param Center $center
     *
     * @return Carbon
     */
    public function getRepromiseDate(Center $center)
    {
        return $this->getCenterQuarter($center)->getRepromiseDate();
    }


    /**
     * Is the current reportingDate the first week of the quarter?
     *
     * @param Region $region
     *
     * @return bool
     */
    public static function isFirstWeek(Region $region)
    {
        $reportingDate = Util::getReportDate();
        $quarter = self::getQuarterByDate($reportingDate, $region);

        if ($quarter) {
            $firstWeek = $quarter->getFirstWeekDate();

            return $firstWeek->eq($reportingDate);
        }

        return false;
    }

    /**
     * Get the current quarter based on region and the current reportingDate
     *
     * @param Region $region
     *
     * @return null|Carbon
     */
    public static function getCurrentQuarter(Region $region)
    {
        $date = Util::getReportDate();

        return static::getQuarterByDate($date, $region);
    }

    /**
     * Get the current quarter for the provided center
     *
     * @param        $id
     * @param Center $center
     *
     * @return null|Quarter
     */
    public static function findForCenter($id, Center $center)
    {
        $key = "quarter:region{$center->regionId}";

        return static::getFromCache($key, $id, function () use ($id, $center) {
            $quarter = Quarter::find($id);
            if ($quarter) {
                $quarter->setRegion($center->region);
            }

            return $quarter;
        });
    }

    /**
     * Get the quarter for the provided date in the provided region
     *
     * @param Carbon $date
     * @param Region $region
     *
     * @return null|Quarter
     */
    public static function getQuarterByDate(Carbon $date, Region $region)
    {
        $dateString = $date->toDateString();
        $key = "quarters:region{$region->id}:dates";

        return static::getFromCache($key, $dateString, function () use ($date, $region) {
            $quarter = Quarter::byRegion($region)
                ->date($date)
                ->first();

            if ($quarter) {
                $quarter->setRegion($region);
            }

            return $quarter;
        });
    }

    /**
     * Get next quarter
     *
     * @return Quarter
     */
    public function getNextQuarter()
    {
        $quarterNumber = ($this->quarterNumber + 1) % 5;
        $quarterNumber = $quarterNumber ?: 1; // no quarter 0

        $year = ($quarterNumber === 1)
            ? $this->year + 1
            : $this->year;

        $quarter = self::year($year)->quarterNumber($quarterNumber)->first();

        if ($this->region) {
            $quarter->setRegion($this->region);
        }

        return $quarter;
    }

    /**
     * List all reporting dates in a center quarter
     * @param  Center|null $center The center
     * @return array               An array of Carbon objects being each reporting week in the quarter
     */
    public function listReportingDates(Center $center = null)
    {
        $output = [];
        $d = $this->getFirstWeekDate($center)->copy();
        $endDate = $this->getQuarterEndDate($center)->copy();
        while ($d->lte($endDate)) {
            $output[] = $d;
            $d = $d->copy()->addWeek();
        }

        return $output;
    }

    /**
     * Set the region for this quarter instance
     *
     * Required before accessing regional quarter dates
     *
     * @param $region
     *
     * @throws Exception
     */
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

    public function scopeDate($query, Carbon $date, Region $region = null)
    {
        if ($region) {
            $this->region = $region;
        }

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

    public function scopeCurrent($query, Region $region = null)
    {
        if ($region) {
            $this->region = $region;
        }

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

    public function scopePresentAndFuture($query, Region $region = null)
    {
        if ($region) {
            $this->region = $region;
        }

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
