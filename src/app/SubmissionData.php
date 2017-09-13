<?php

namespace TmlpStats;

use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class SubmissionData extends Model
{
    use CamelCaseModel;

    protected $guarded = ['id'];
    protected $table = 'submission_data';
    protected $casts = [
        'data' => 'json',
        'reporting_date' => 'date',
    ];

    /**
     * Returns true if this has been soft deleted
     * @return boolean [description]
     */
    public function isSoftDeleted(): bool
    {
        return array_get($this->data, '__deleted', false);
    }

    public function scopeCenter($query, $center)
    {
        if (!is_numeric($center)) {
            $center = $center->id;
        }

        return $query->where('center_id', $center);
    }

    public function scopeCenterDate($query, $center, $reportingDate)
    {
        return $this->scopeCenter($query, $center)
                    ->where('reporting_date', $reportingDate);
    }

    // Scope by either the type code or the class name representing the type code.
    public function scopeType($query, $type)
    {
        return $query->where('stored_type', $type);
    }

    public function scopeTypeId($query, $type, $id)
    {
        return $this->scopeType($query, $type)
                    ->where('stored_id', $id);
    }

}
