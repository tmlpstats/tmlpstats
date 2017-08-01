<?php
namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class Transfer extends Model
{
    use CamelCaseModel;

    protected $guarded = ['id'];
    protected $table = 'transfers';

    public function scopeReportingDate($query, Carbon $date, Carbon $end = null)
    {
        if ($end) {
            return $query->where('reporting_date', '>=', $date)
                         ->where('reporting_date', '<=', $end);
        }

        return $query->whereReportingDate($date);
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function scopeByStatsReport($query, StatsReport $statsReport)
    {
        return $query->whereStatsReportId($statsReport->id);
    }

    public function scopeBySubject($query, $type, $subject)
    {
        $query = $query->whereSubjectType($type);

        // Search by array of ids
        if (is_array($subject)) {
            return $query->whereIn('subject_id', $subject);
        }

        if (is_object($subject)) {
            $subject = $subject->id;
        }

        return $query->whereSubjectId($subject);
    }
}
