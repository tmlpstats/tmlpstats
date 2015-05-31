<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

use DB;
use Log;
use TmlpStats\Quarter;

class StatsReport extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'center_id',
        'quarter_id',
        'reporting_date',
        'spreadsheet_version',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function isValidated()
    {
        return (bool) $this->validated;
    }

    // Flush all objects created by this report.
    public function clear()
    {
        $this->centerStatsId = null;
        $this->reportingStatisticianId = null;
        $this->save();
        try {
            DB::table('center_stats')
                ->where('stats_report_id', '=', $this->id)->delete();
            DB::table('center_stats_data')
                ->where('stats_report_id', '=', $this->id)->delete();

            DB::table('courses_data')
                ->where('stats_report_id', '=', $this->id)->delete();
            DB::table('courses')
                ->where('stats_report_id', '=', $this->id)->delete();

            DB::table('program_team_members')
                ->where('stats_report_id', '=', $this->id)->delete();

            DB::table('team_members_data')
                ->where('stats_report_id', '=', $this->id)->delete();
            DB::table('team_members')
                ->where('stats_report_id', '=', $this->id)->delete();

            DB::table('tmlp_games_data')
                ->where('stats_report_id', '=', $this->id)->delete();
            DB::table('tmlp_games')
                ->where('stats_report_id', '=', $this->id)->delete();

            DB::table('tmlp_registrations_data')
                ->where('stats_report_id', '=', $this->id)->delete();
            DB::table('tmlp_registrations')
                ->where('stats_report_id', '=', $this->id)->delete();
        } catch(\Exception $e) {
            Log::error("Error clearing statsReport {$this->id}: " . $e->getMessage());
        }
    }

    public function scopeReportingDate($query, $date)
    {
        return $query->whereReportingDate($date);
    }

    public function scopeCenter($query, $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeCurrentQuarter($query)
    {
        $quarter = Quarter::current()->first();
        return $query->whereQuarterId($quarter->id);
    }

    public function centerStats()
    {
        return $this->hasOne('TmlpStats\CenterStats');
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }
}
