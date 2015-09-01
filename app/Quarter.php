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

    public function __get($name)
    {
        switch ($name) {
            case 'startWeekendDate':
            case 'endWeekendDate':
            case 'classroom1Date':
            case 'classroom2Date':
            case 'classroom3Date':
                // TODO: remove check. needed for migration
                if (\DB::table('migrations')->whereMigration('2015_08_30_000400_cleanup')->count() == 0) {
                    return parent::__get($name);
                }

                if ($this->region && !$this->regionQuarterDetails) {
                    $this->setRegion($this->region);
                }
                if (!$this->regionQuarterDetails) {
                    throw new Exception("Cannot call __get({$name}) before setting region.");
                }
                return $this->regionQuarterDetails->$name;

            default:
                return parent::__get($name);
        }
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
        $date = Carbon::now();
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
        $date = Carbon::now();
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
