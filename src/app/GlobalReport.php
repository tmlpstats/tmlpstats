<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

use Carbon\Carbon;
use Cache;

class GlobalReport extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = [
        'reporting_date',
        'quarter_id',
        'user_id',
        'locked',
    ];

    protected $dates = [
        'reporting_date',
    ];

    protected $casts = [
        'locked' => 'boolean',
    ];

    public function addCenterReport(StatsReport $report)
    {
        $existingReport = $this->statsReports()->byCenter($report->center)->first();
        if ($existingReport) {
            $this->statsReports()->detach($existingReport->id);
            Cache::tags(["statsReport{$existingReport->id}"])->flush();
        }
        $this->statsReports()->attach($report->id);

        Cache::tags(["globalReport{$this->id}"])->flush();
    }

    public function getStatsReportByCenter(Center $center)
    {
        foreach ($this->statsReports as $report) {
            if ($report->centerId == $center->id) {
                return $report;
            }
        }
        return null;
    }

    public function scopeReportingDate($query, Carbon $date)
    {
        return $query->whereReportingDate($date);
    }

    public function scopeBetween($query, Carbon $start, Carbon $end)
    {
        return $query->where('reporting_date', '>=', $start)
                     ->where('reporting_date', '<=', $end);
    }


    public function updatedByUser()
    {
        return $this->hasOne('TmlpStats\User');
    }

    public function statsReports()
    {
        return $this->belongsToMany('TmlpStats\StatsReport', 'global_report_stats_report')->withTimestamps();
    }

    public function reportToken()
    {
        return $this->morphOne('TmlpStats\ReportToken', 'report');
    }
}
