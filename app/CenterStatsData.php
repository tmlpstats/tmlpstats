<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class CenterStatsData extends Model {

    use CamelCaseModel;

    protected $table = 'center_stats_data';

    protected $fillable = [
        'stats_report_id',
        'reporting_date',
        'type',
        'cap',
        'cpc',
        't1x',
        't2x',
        'gitw',
        'lf',
        'tdo',
        'points',
        'program_manager_attending_weekend',
        'classroom_leader_attending_weekend',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function setReportingDateAttribute($value)
    {
        $date = $this->asDateTime($value);
        $this->attributes['reporting_date'] = $date->toDateString();
    }

    public function scopeStatsReport($query, $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function scopeReportingDate($query, $date)
    {
        if ($date instanceof \Carbon\Carbon) {
            $date = $date->toDateString();
        }
        return $query->whereReportingDate($date);
    }

    public function scopeActual($query)
    {
        return $query->whereType('actual');
    }

    public function scopePromise($query)
    {
        return $query->whereType('promise');
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport')->withTimestamps();
    }
}
