<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class TmlpGameData extends Model
{
    use CamelCaseModel;

    protected $table = 'tmlp_games_data';

    protected $fillable = [
        'stats_report_id',
        'type',
        'quarter_start_registered',
        'quarter_start_approved',
    ];

    public function scopeType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopeIncomingT1($query)
    {
        return $query->whereType('Incoming T1');
    }

    public function scopeIncomingT2($query)
    {
        return $query->whereType('Incoming T2');
    }

    public function scopeFutureT1($query)
    {
        return $query->whereType('Future T1');
    }

    public function scopeFutureT2($query)
    {
        return $query->whereType('Future T2');
    }

    public function scopeT1($query)
    {
        return $query->where('type', 'like', '% T1');
    }

    public function scopeT2($query)
    {
        return $query->where('type', 'like', '% T2');
    }

    public function scopeByStatsReport($query, StatsReport $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport');
    }
}
