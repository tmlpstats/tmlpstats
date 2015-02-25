<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class CourseData extends Model {

    use CamelCaseModel;

    protected $table = 'courses_data';

    protected $fillable = [
        'center_id',
        'quarter_id',
        'reporting_date',
        'course_id',
        'offset',
        'quarter_start_ter',
        'quarter_start_standard_starts',
        'quarter_start_xfer',
        'current_ter',
        'current_standard_starts',
        'current_xfer',
        'completed_standard_starts',
        'potentials',
        'registrations',
    ];

    protected $dates = [
        'reporting_date',
    ];

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function quarter()
    {
        return $this->belongsTo('TmlpStats\Quarter');
    }

    public function course()
    {
        return $this->hasOne('TmlpStats\Course');
    }
}
