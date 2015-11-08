<?php

namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Illuminate\Database\Eloquent\Model;

class ReportToken extends Model
{
    use CamelCaseModel;

    protected $fillable = array(
        'token',
        'report_id',
        'report_type',
        'center_id',
        'expires_at',
    );

    public function getReportPath()
    {
        $globalReport = GlobalReport::findOrFail($this->reportId);

        $reportUrl = null;
        if ($this->center) {
            $statsReport = $globalReport->getStatsReportByCenter($this->center);
            if ($statsReport) {
                $reportUrl = "statsreports/{$statsReport->id}";
            }
        } else {
            $reportUrl = "globalreports/{$globalReport->id}";
        }
        return $reportUrl;
    }

    public function isValid()
    {
        return !$this->expiresAt || $this->expiresAt->gt(Carbon::now());
    }

    public function scopeToken($query, $token)
    {
        return $query->whereToken($token);
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>=', Carbon::now());
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereCenterId($center->id);
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }
}
