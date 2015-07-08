<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

use Carbon\Carbon;
use TmlpStats\Quarter;

class GlobalReport extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'reporting_date',
        'quarter_id',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function scopeReportingDate($query, $date)
    {
        return $query->whereReportingDate($date);
    }

    public function scopeCurrentQuarter($query, $region)
    {
        $quarter = Quarter::findByDateAndRegion(Carbon::now(), $region);
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeLastQuarter($query, $region)
    {
        $currentQuarter = Quarter::findByDateAndRegion(Carbon::now(), $region);
        $lastQuarter = Quarter::findByDateAndRegion($currentQuarter->startWeekendDate, $region);
        return $query->whereQuarterId($lastQuarter->id);
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }

    public function updatedByUser()
    {
        return $this->hasOne('TmlpStats\User');
    }

    public function statsReports()
    {
        return $this->belongsToMany('TmlpStats\StatsReport', 'global_report_stats_report')->withTimestamps();
    }
}
