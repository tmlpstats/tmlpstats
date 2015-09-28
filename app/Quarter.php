<?php
namespace TmlpStats;

use TmlpStats\Region;
use TmlpStats\RegionQuarterDetails;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;
use Carbon\Carbon;

use DB;
use Exception;

class Quarter extends Model
{
    use CamelCaseModel;

    protected $fillable = array(
        't1_distinction',
        't2_distinction',
        'quarter_number',
        'year',
    );

    protected $region = null;
    protected $regionQuarterDetails = null;

    public function setRegion($region)
    {
        if (!$this->region) {
            throw new Exception('Cannot set empty region.');
        }

        $this->region = $region;

        $this->regionQuarterDetails = RegionQuarterDetails::quarter($this)
            ->region($this->region)
            ->first();
    }

    public function getStartWeekendDate()
    {
        if (!$this->regionQuarterDetails) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->regionQuarterDetails->startWeekendDate);
    }

    public function getEndWeekendDate()
    {
        if (!$this->regionQuarterDetails) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->regionQuarterDetails->endWeekendDate);
    }

    public function getClassroom1Date()
    {
        if (!$this->regionQuarterDetails) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->regionQuarterDetails->classroom1Date);
    }

    public function getClassroom2Date()
    {
        if (!$this->regionQuarterDetails) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->regionQuarterDetails->classroom2Date);
    }

    public function getClassroom3Date()
    {
        if (!$this->regionQuarterDetails) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->regionQuarterDetails->classroom3Date);
    }

    public function scopeQuarterNumber($query, $number)
    {
        return $query->whereQuarterNumber($number);
    }

    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeRegion($query, Region $region)
    {
        $this->region = $region;
        return $query->whereIn('id', function($query) use ($region) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('region_id', $region->id)
                ->orWhere('region_id', $region->parentId);
        });
    }

    public function scopeDate($query, Carbon $date)
    {
        $query->whereIn('id', function($query) use ($date) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('start_weekend_date', '<', $date->startOfDay()->toDateString())
                ->where('end_weekend_date', '>=', $date->startOfDay()->toDateString());

            if ($this->region) {
                $query->where(function($query) {
                    $query->where('region_id', $this->region->id)
                        ->orWhere('region_id', $this->region->parentId);
                });
            }
        });
    }

    public function scopeCurrent($query)
    {
        $date = Carbon::now();
        return $query->whereIn('id', function ($query) use ($date) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('start_weekend_date', '<', $date->startOfDay())
                ->where('end_weekend_date', '>=', $date->startOfDay());
        });
    }

    public function scopePresentAndFuture($query)
    {
        $date = Carbon::now();
        return $query->whereIn('id', function ($query) use ($date) {
            $query->select('quarter_id')
                ->from('region_quarter_details')
                ->where('end_weekend_date', '>=', $date->startOfDay());
        });
    }

    public function statsReport()
    {
        return $this->hasMany('TmlpStats\StatsReport');
    }
}
