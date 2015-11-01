<?php
namespace TmlpStats;

use Cache;
use TmlpStats\StatsReport;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

use Carbon\Carbon;
use TmlpStats\Quarter;

class GlobalReport extends Model
{
    use CamelCaseModel;

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
        }
        $this->statsReports()->attach($report->id);

        Cache::tags(["globalReport{$this->id}"])->flush();
    }

    public function getStatsReportByCenter(Center $center)
    {
        return $this->statsReports()->byCenter($center)->first();
    }

    public function scopeReportingDate($query, Carbon $date)
    {
        return $query->whereReportingDate($date);
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
