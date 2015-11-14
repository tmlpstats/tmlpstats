<?php

namespace TmlpStats;

use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use TmlpStats\Traits\CachedRelationships;


class ReportToken extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'token',
        'report_id',
        'report_type',
        'center_id',
        'expires_at',
    );

    /**
     * Fetch or create a ReportToken for the given report.
     *
     * @param Model $report           Report object must be derived from Model
     * @param Center|null $center     If null, token will authorize all child reports
     * @param Carbon|null $expiresAt  If null, token will never expire
     * @return static
     * @throws Exception
     */
    public static function get(Model $report, Center $center = null, Carbon $expiresAt = null)
    {
        if (!($report instanceof GlobalReport)) {
            // Report tokens only support global reports right now. If you want to support other
            // types, you'll have to update getReportPath to handle it properly.
            throw new Exception('ReportToken only supports global reports.');
        }

        $reportToken = ReportToken::byReport($report)->byCenter($center)->first();

        if (!$reportToken) {
            $reportToken = ReportToken::create([
                'report_id'   => $report->id,
                'report_type' => get_class($report),
                'center_id'   => $center ? $center->id : null,
                'expires_at'  => $expiresAt ? $expiresAt->toDateString() : null,
                'token'       => Util::getRandomString(),
            ]);
        }

        return $reportToken;
    }

    /**
     * Get the path string to use with url() method
     *
     * @return null|string
     */
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

    /**
     * Get the full URL for using this token
     *
     * @return string
     */
    public function getUrl()
    {
        return url("report/{$this->token}");
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

    public function scopeByReport($query, Model $report)
    {
        return $query->whereReportId($report->id)->whereReportType(get_class($report));
    }


    public function scopeByCenter($query, Center $center = null)
    {
        if ($center) {
            return $query->whereCenterId($center->id);
        } else {
            return $query->whereNull('center_id');
        }
    }

    public function center()
    {
        return $this->belongsTo('TmlpStats\Center');
    }

    public function report()
    {
        return $this->morphTo();
    }
}
