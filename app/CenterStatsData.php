<?php
namespace TmlpStats;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class CenterStatsData extends Model
{
    use CamelCaseModel;

    protected $table = 'center_stats_data';

    protected $fillable = [
        'stats_report_id',
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

    public function scopeActual($query)
    {
        return $query->whereType('actual');
    }

    public function scopePromise($query)
    {
        return $query->whereType('promise');
    }

    public function scopeStatsReport($query, StatsReport $statsReport)
    {
        return $query->where('id', function($query) use ($statsReport) {
            $query->select('actual_data_id')
                ->from('center_stats')
                ->where('stats_report_id', $statsReport->id);
        });
    }

    public function centerStats()
    {
        return $this->belongsTo('TmlpStats\CenterStats');
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport');
    }
}
