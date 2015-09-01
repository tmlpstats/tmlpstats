<?php
namespace TmlpStats;

use TmlpStats\Quarter;
use TmlpStats\CenterStatsData;
use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

use Carbon\Carbon;

class StatsReport extends Model
{
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
        'locked'    => 'boolean',
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
        return (bool)$this->validated;
    }

    public function isSubmitted()
    {
        return $this->submitted_at !== null;
    }

    public function getPoints()
    {
        $data = CenterStatsData::actual()->byStatsReport($this)->first();
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

    public function scopeReportingDate($query, Carbon $date)
    {
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

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeCurrentQuarter($query, Region $region = null)
    {
        $quarter = Quarter::byRegion($region)->date(Carbon::now())->first();
        if (!$quarter) {
            return $query;
        }
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeLastQuarter($query, Region $region = null)
    {
        $currentQuarter = Quarter::byRegion($region)->date(Carbon::now())->first();
        if (!$currentQuarter) {
            return $query;
        }
        $currentQuarter->setRegion($region);

        $lastQuarter = Quarter::byRegion($region)->date($currentQuarter->startWeekendDate)->first();
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
