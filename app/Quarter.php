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
    );

    protected $dates = [
        'start_weekend_date',
        'end_weekend_date',
        'classroom1_date',
        'classroom2_date',
        'classroom3_date',
    ];

    public static function findByDate($date)
    {
        return static::where('start_weekend_date', '<', $date->toDateString())
                     ->where('end_weekend_date', '>=', $date->toDateString())
                     ->first();
    }

    public function scopeCurrent($query)
    {
        return $query->where('end_weekend_date', '>=', Carbon::now());
    }

    public function statsReport()
    {
        return $this->hasMany('TmlpStats\StatsReport')->withTimestamps();
    }
}
