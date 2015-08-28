<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

use Carbon\Carbon;

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
        'submitted_at',
    ];

    public function setReportingDateAttribute($value)
    {
        $date = $this->asDateTime($value);
        $this->attributes['reporting_date'] = $date->toDateString();
    }

    public function isValidated()
    {
        return (bool) $this->validated;
    }

    public function isSubmitted()
    {
        return $this->submitted_at !== null;
    }

    // Flush all objects created by this report.
    public function clear()
    {
        // Do not clear if locked!
        if ($this->locked) {
            return false;
        }

        $success = true;
        $tables = array(
        //  Table Name                Ignore errors encountered while deleting
            'center_stats'            => false,
            'center_stats_data'       => false,
            'courses_data'            => false,
            'courses'                 => true,
            'program_team_members'    => false,
            'team_members_data'       => false,
            'team_members'            => true,
            'tmlp_games_data'         => false,
            'tmlp_games'              => true,
            'tmlp_registrations_data' => false,
            'tmlp_registrations'      => true,
        );

        $this->centerStatsId = null;
        $this->reportingStatisticianId = null;
        $this->save();

        foreach ($tables as $table => $ignoreErrors) {
            try {
                DB::table($table)->where('stats_report_id', '=', $this->id)
                                 ->delete();
            } catch(\Exception $e) {
                Log::error("Error clearing statsReport {$this->id} from $table table: " . $e->getMessage());

                if (!$ignoreErrors) {
                    $success = false;
                }
            }
        }

        return $success;
    }

    public function getRating()
    {
        $centerStats = CenterStats::find($this->centerStatsId);
        return $centerStats ? $centerStats->actualData->rating : null;
    }

    public function scopeReportingDate($query, $date)
    {
        if (is_object($date)) {
            $date = $date->toDateString();
        }
        return $query->whereReportingDate($date);
    }

    public function scopeValidated($query, $validated = true)
    {
        return $query->whereValidated($validated);
    }

    public function scopeSubmitted($query, $submitted = true)
    {
        if ($submitted) {
            return $query->whereNotNull('submitted_at');
        } else {
            return $query->whereNull('submitted_at');
        }
    }

    public function scopeCenter($query, $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeCurrentQuarter($query, $region)
    {
        $quarter = Quarter::findByDateAndRegion(Carbon::now(), $region);
        if (!$quarter) {
            return $query;
        }
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeLastQuarter($query, $region)
    {
        $currentQuarter = Quarter::findByDateAndRegion(Carbon::now(), $region);
        if (!$currentQuarter) {
            return $query;
        }
        $lastQuarter = Quarter::findByDateAndRegion($currentQuarter->startWeekendDate, $region);
        if (!$lastQuarter) {
            return $query;
        }
        return $query->whereQuarterId($lastQuarter->id);
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

    public function globalReports()
    {
        return $this->belongsToMany('TmlpStats\GlobalReport', 'global_report_stats_report')->withTimestamps();
    }

}
