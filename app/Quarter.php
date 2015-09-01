<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;
use Carbon\Carbon;

use DB;
use Exception;

class Quarter extends Model {

    use CamelCaseModel;

    protected $fillable = array(
        't1_distinction',
        't2_distinction',
        'quarter_number',
        'year',
    );

    protected $region = null;
    protected $quarterRegion = null;

    public function setRegion($region)
    {
        if (!$this->region) {
            // TODO: barf
        }

        $this->region = $region;

        $this->quarterRegion = DB::table('quarter_region')
                                 ->where('quarter_id', $this->id)
                                 ->where('region_id', $this->region)
                                 ->first();
    }

    public function getStartWeekendDate()
    {
        if (!$this->quarterRegion) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->quarterRegion->startWeekendDate);
    }

    public function getEndWeekendDate()
    {
        if (!$this->quarterRegion) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->quarterRegion->endWeekendDate);
    }

    public function getClassroom1Date()
    {
        if (!$this->quarterRegion) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->quarterRegion->classroom1Date);
    }

    public function getClassroom2Date()
    {
        if (!$this->quarterRegion) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->quarterRegion->classroom2Date);
    }

    public function getClassroom3Date()
    {
        if (!$this->quarterRegion) {
            throw new Exception('Cannot call ' . __FUNCTION__ . ' before setting region.');
        }
        return Carbon::createFromFormat('Y-m-d', $this->quarterRegion->classroom3Date);
    }

    public static function filterByDate($date)
    {
        return static::where('start_weekend_date', '<', $date->toDateString())
                     ->where('end_weekend_date', '>=', $date->toDateString());
    }

    public function scopeQuarterNumber($query, $number)
    {
        return $query->whereQuarterNumber($number);
    }

    public function scopeYear($query, $year)
    {
        return $query->where('year', $year);
    }

    public function scopeRegion($query, $region)
    {
        return $query->where('region_id', $region->id);
    }

    public function scopePresentAndFuture($query, $region)
    {
        return $query->where('end_weekend_date', '>=', Carbon::now()->startOfDay());
    }

    public function scopeCurrent($query, $region)
    {
        return $query->where('start_weekend_date', '<', Carbon::now()->startOfDay())
                     ->where('end_weekend_date', '>=', Carbon::now()->startOfDay());
    }

    public function statsReport()
    {
        return $this->hasMany('TmlpStats\StatsReport');
    }
}
