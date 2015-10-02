<?php
namespace TmlpStats;

use TmlpStats\Quarter;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;
use Carbon\Carbon;

use DB;
use Exception;

class RegionQuarterDetails extends Model
{
    use CamelCaseModel;

    protected $fillable = array(
        'quarter_id',
        'region_id',
        'location',
        'start_weekend_date',
        'end_weekend_date',
        'classroom1_date',
        'classroom2_date',
        'classroom3_date',
    );

    protected $dates = array(
        'start_weekend_date',
        'end_weekend_date',
        'classroom1_date',
        'classroom2_date',
        'classroom3_date',
    );

    public function scopeByQuarter($query, Quarter $quarter)
    {
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeByRegion($query, Region $region)
    {
        return $query->where(function($query) use ($region) {
            $query->where('region_id', $region->id)
                ->orWhere('region_id', $region->parentId);
        });
    }

    public function scopeByDate($query, Carbon $date)
    {
        return $query->where('start_weekend_date', '<', $date->startOfDay())
            ->where('end_weekend_date', '>=', $date->startOfDay());
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_weekend_date', '<', Carbon::now()->startOfDay())
            ->where('end_weekend_date', '>=', Carbon::now()->startOfDay());
    }

    public function scopePresentAndFuture($query)
    {
        return $query->where('end_weekend_date', '>=', Carbon::now()->startOfDay());
    }

    public function scopePast($query)
    {
        return $query->where('end_weekend_date', '<', Carbon::now()->startOfDay());
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }

    public function region()
    {
        return $this->belongsTo('TmlpStats\Region');
    }
}
