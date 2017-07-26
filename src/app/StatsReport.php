<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Domain\ReportDeadlines;
use TmlpStats\Domain\ScoreboardGame;
use TmlpStats\Traits\CachedRelationships;

class StatsReport extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $reportDeadlines = null;

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
        'validation_messages' => 'json',
    ];

    public function __get($name)
    {
        if ($name === 'quarter') {
            return Quarter::findForCenter($this->quarterId, $this->center);
        }

        return parent::__get($name);
    }

    /**
     * Was this report submitted on or before the deadline?
     *
     * @return bool
     */
    public function isOnTime()
    {
        $submittedAt = $this->submittedAt->copy();
        $submittedAt->setTimezone($this->center->timezone);

        return $submittedAt->lte($this->due());
    }

    /**
     * Get datetime object for when stats are due
     *
     * @return null|Carbon date
     */
    public function due()
    {
        if (!$this->reportDeadlines) {
            $this->reportDeadlines = ReportDeadlines::get($this->center, $this->quarter, $this->reportingDate);
        }

        $due = $this->reportDeadlines['report'];
        if (!$due) {
            $due = Carbon::create(
                $this->reportingDate->year,
                $this->reportingDate->month,
                $this->reportingDate->day,
                19, 0, 59,
                $this->center->timezone
            );
        }

        return $due;
    }

    /**
     * Get datetime object for when the regional statistician response is due
     *
     * @return null|Carbon date
     */
    public function responseDue()
    {
        if (!$this->reportDeadlines) {
            $this->reportDeadlines = ReportDeadlines::get($this->center, $this->quarter, $this->reportingDate);
        }

        $due = $this->reportDeadlines['response'];

        // No response required on last week
        $quarterEndDate = $this->quarter->getQuarterEndDate($this->center);
        if (!$due && $quarterEndDate && $this->reportingDate->eq($quarterEndDate)) {
            return null;
        }

        if (!$due) {
            $due = Carbon::create(
                $this->reportingDate->year,
                $this->reportingDate->month,
                $this->reportingDate->day + 1,
                10, 0, 0,
                $this->center->timezone
            );
        }

        return $due;
    }

    /**
     * Did this report pass validation?
     *
     * @return bool
     */
    public function isValidated()
    {
        return $this->validated;
    }

    /**
     * Was this report officially submitted
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->submitted_at !== null;
    }

    /**
     * Get the points for this reporting week
     *
     * @return null|integer
     */
    public function getPoints()
    {
        $data = CenterStatsData::byStatsReport($this)
            ->reportingDate($this->reportingDate)
            ->actual()
            ->first();

        return $data ? $data->points : null;
    }

    /**
     * Get the rating for this reporting week
     *
     * @return null|string
     */
    public function getRating()
    {
        $points = $this->getPoints();

        if ($points === null) {
            return null;
        }

        return ScoreboardGame::getRating($points);
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

    public function scopeOfficial($query)
    {
        return $query->whereIn('id', function ($query) {
            $query->select('stats_report_id')
                  ->from('global_report_stats_report');
        });
    }

    public function scopeByQuarter($query, Quarter $quarter)
    {
        return $query->whereQuarterId($quarter->id);
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeCurrentQuarter($query, Region $region = null)
    {
        $quarter = Quarter::getQuarterByDate(Util::getReportDate(), $region);
        if (!$quarter) {
            return $query;
        }

        return $query->whereQuarterId($quarter->id);
    }

    public function scopeLastQuarter($query, Region $region = null)
    {
        $currentQuarter = Quarter::getQuarterByDate(Util::getReportDate(), $region);
        if (!$currentQuarter) {
            return $query;
        }

        $lastQuarter = Quarter::getQuarterByDate($currentQuarter->getQuarterStartDate(), $region);
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

    public function tmlpRegistrationData()
    {
        return $this->hasMany('TmlpStats\TmlpRegistrationData');
    }

    public function centerStatsData()
    {
        return $this->hasMany('TmlpStats\CenterStatsData');
    }

    public function getUriLocalReport()
    {
        $reportingDate = $this->reportingDate;
        if ($reportingDate instanceof Carbon) {
            $reportingDate = $reportingDate->toDateString();
        }

        return action('ReportsController@getCenterReport',
            [
                'abbr' => $this->center->abbrLower(),
                'reportingDate' => $reportingDate,
            ]
        );
    }
}
