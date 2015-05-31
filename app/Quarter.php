<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;
use Carbon\Carbon;

class Quarter extends Model {

    use CamelCaseModel;

	protected $fillable = array(
        'start_weekend_date',
        'end_weekend_date',
        'classroom1_date',
        'classroom2_date',
        'classroom3_date',
        'location',
        'distinction',
        'global_region',
        'local_region',
    );

    protected $dates = [
        'start_weekend_date',
        'end_weekend_date',
        'classroom1_date',
        'classroom2_date',
        'classroom3_date',
    ];

    public static function findByDateAndRegion($date, $region)
    {
        return static::where('start_weekend_date', '<', $date->toDateString())
                     ->where('end_weekend_date', '>=', $date->toDateString())
                     ->where('global_region', $region)
                     ->first();
    }

    public function scopeCurrent($query, $region)
    {
        return $query->where('start_weekend_date', '<', Carbon::now())
                     ->where('end_weekend_date', '>=', Carbon::now())
                     ->where('global_region', $region);
    }

    public function statsReport()
    {
        return $this->hasMany('TmlpStats\StatsReport')->withTimestamps();
    }
}
