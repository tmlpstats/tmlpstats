<?php
namespace TmlpStats;

use TmlpStats\Quarter;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

class StatsReport extends ModelCachedRelationships
{
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
            return Quarter::findForCenter($this->quarterId, $this->center);
        }
        return parent::__get($name);
    }

    public function setReportingDateAttribute($value)
    {
        $date = $this->asDateTime($value);
        $this->attributes['reporting_date'] = $date->toDateString();
    }

    public function isOnTime()
    {
        $submittedAt = clone $this->submittedAt;
        $submittedAt->setTimezone($this->center->timezone);

        return $submittedAt->lte($this->due());
    }

    public function due()
    {
        // TODO: make this configurable
        if ($this->reportingDate->eq($this->quarter->classroom2Date)) {
            // Stats are due by 11:59 PM at the second classroom
            // 11:59.59 PM local time on the reporting date.
            return Carbon::create(
                $this->reportingDate->year,
                $this->reportingDate->month,
                $this->reportingDate->day,
                23, 59, 59,
                $this->center->timezone
            );
        } else {
            // Stats are due by 7:00 PM every other week
            // 7:00.59 PM local time on the reporting date.
            return Carbon::create(
                $this->reportingDate->year,
                $this->reportingDate->month,
                $this->reportingDate->day,
                19, 0, 59,
                $this->center->timezone
            );
        }
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

        return static::pointsToRating($points);
    }

    public static function calculatePercent($actual, $promise)
    {
        return $promise > 0
            ? max(min(round(($actual / $promise) * 100), 100), 0)
            : 0;
    }

    public static function pointsByPercent($percent, $game)
    {
        $points = 0;

        if ($percent == 100) {
            $points = 4;
        } else if ($percent >= 90) {
            $points = 3;
        } else if ($percent >= 80) {
            $points = 2;
        } else if ($percent >= 75) {
            $points = 1;
        }

        return ($game == 'cap') ? $points * 2 : $points;
    }

    public static function pointsToRating($points)
    {
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

    public function scopeByRegion($query, Region $region)
    {
        $childRegions = $region->getChildRegions();
        $searchRegionIds = [];
        if ($childRegions) {
            foreach ($childRegions as $child) {
                $searchRegionIds[] = $child->id;
            }
        }
        $searchRegionIds[] = $region->id;

        return $query->whereIn('center_id', function ($query) use ($searchRegionIds) {
            $query->select('id')
                ->from('centers')
                ->whereIn('region_id', $searchRegionIds);
        });
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
        $quarter = Quarter::getQuarterByDate(Carbon::now(), $region);
        if (!$quarter) {
            return $query;
        }
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeLastQuarter($query, Region $region = null)
    {
        $currentQuarter = Quarter::getQuarterByDate(Carbon::now(), $region);
        if (!$currentQuarter) {
            return $query;
        }

        $lastQuarter = Quarter::getQuarterByDate($currentQuarter->startWeekendDate, $region);
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
        return $this->belongsTo('TmlpStats\User');
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
