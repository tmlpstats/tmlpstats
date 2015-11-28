<?php
namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;

class CourseData extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $table = 'courses_data';

    protected $fillable = [
        'stats_report_id',
        'course_id',
        'quarter_start_ter',
        'quarter_start_standard_starts',
        'quarter_start_xfer',
        'current_ter',
        'current_standard_starts',
        'current_xfer',
        'completed_standard_starts',
        'potentials',
        'registrations',
        'guests_promised',
        'guests_invited',
        'guests_confirmed',
        'guests_attended',
    ];

    public function __get($name)
    {
        switch ($name) {
            case 'center':
                return $this->course->$name;
            default:
                return parent::__get($name);
        }
    }

    public function scopeByStatsReport($query, $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function statsReport()
    {
        return $this->belongsTo('TmlpStats\StatsReport');
    }

    public function course()
    {
        return $this->belongsTo('TmlpStats\Course');
    }
}
