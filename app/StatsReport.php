<?php
namespace TmlpStats;

use TmlpStats\Quarter;
use TmlpStats\CenterStatsData;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

use Carbon\Carbon;

use DB;
use Log;

class StatsReport extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'reporting_date',
        'center_id',
        'quarter_id',
        'user_id',
        'version',
        'validated',
        'locked',
        'submitted_at',
        'submit_comment',
    ];

    protected $dates = [
        'reporting_date',
        'submitted_at',
    ];

    protected $casts = [
        'validated' => 'boolean',
        'locked' => 'boolean',
    ];

    public function __get($name)
    {
        if ($name === 'quarter') {
            $quarter = parent::__get('quarter');
            $quarter->setRegion($this->center->region);
            return $quarter;
        }
        return parent::__get($name);
    }

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

    // TODO: Need to address this with new schema
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

    public function getPoints()
    {
        $data = CenterStatsData::actual()->statsReport($this)->first();
        return $data ? $data->points : null;
    }

    public function getRating()
    {
        $points = $this->getPoints();

        if ($points === null) {
            return null;
        }

        if ($points == 28) {
            return "Powerful";
        } else if ($points >= 22) {
            return "High Performing";
        } else if ($points >= 16) {
            return "Effective";
        } else if ($points >= 9) {
            return "Marginally Effective";
        } else {
            return "Ineffective";
        }
    }

    public function scopeReportingDate($query, $date)
    {
        if ($date instanceof Carbon) {
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

    public function scopeCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeCurrentQuarter($query, $region)
    {
        $quarter = Quarter::region($region)->date(Carbon::now())->first();
        if (!$quarter) {
            return $query;
        }
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeLastQuarter($query, $region)
    {
        $currentQuarter = Quarter::region($region)->date(Carbon::now())->first();
        if (!$currentQuarter) {
            return $query;
        }
        $lastQuarter = Quarter::region($region)->date($currentQuarter->startWeekendDate)->first();
        if (!$lastQuarter) {
            return $query;
        }
        return $query->whereQuarterId($lastQuarter->id);
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }

    public function user()
    {
        return $this->hasOne('TmlpStats\User');
    }

    public function globalReports()
    {
        return $this->belongsToMany('TmlpStats\GlobalReport', 'global_report_stats_report')->withTimestamps();
    }

    public function courseData()
    {
        return $this->hasMany('TmlpStats\CourseData');
    }

    public function teamMemberData()
    {
        return $this->hasMany('TmlpStats\TeamMemberData');
    }

    public function teamRegistrationData()
    {
        return $this->hasMany('TmlpStats\TeamRegistrationData');
    }

    public function centerStatsData()
    {
        return $this->hasMany('TmlpStats\CenterStatsData');
    }

    public function tmlpGamesData()
    {
        return $this->hasMany('TmlpStats\TmlpGamesData');
    }
}
