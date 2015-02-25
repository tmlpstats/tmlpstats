<?php
namespace TmlpStats;

use Illuminate\Database\Eloquent\Model;
use Eloquence\Database\Traits\CamelCaseModel;

class Course extends Model {

    use CamelCaseModel;

    protected $fillable = [
        'center_id',
        'start_date',
        'type',
    ];

    protected $dates = [
        'start_date',
    ];

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function courseData()
    {
        return $this->hasMany('TmlpStats\CourseData');
    }
}
