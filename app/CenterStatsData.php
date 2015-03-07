<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class CenterStatsData extends Model {

    use CamelCaseModel;

    protected $table = 'center_stats_data';

    protected $fillable = [
        'reporting_date',
        'type',
        'offset',
        'center_id',
        'quarter_id',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function scopeStatsReport($query, $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function scopeCenter($query, $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeReportingDate($query, $date)
    {
        return $query->whereReportingDate($date);
    }

    public function scopeActual($query)
    {
        return $query->whereType('actual');
    }

    public function centerStats()
    {
        // Only true for Actuals
        if ($this->type == 'actual') {
            return $this->belongsTo('TmlpStats\CenterStats');
        } else {
            throw new \Exception('Center Stats Data of type promise does not have a belongsTo relationship with Center Stats');
        }
    }
}
