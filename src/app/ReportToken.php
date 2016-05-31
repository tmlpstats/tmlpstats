<?php
namespace TmlpStats;

use App;
use Carbon\Carbon;
use Eloquence\Database\Traits\CamelCaseModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Request;
use TmlpStats\Http\Controllers\Controller;
use TmlpStats\Traits\CachedRelationships;

class ReportToken extends Model
{
    use CamelCaseModel, CachedRelationships;

    protected $fillable = array(
        'token',
        'report_id',
        'report_type',
        'owner_id',
        'owner_type',
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
    public static function get(Model $report, Model $owner = null, Carbon $expiresAt = null)
    {
        if (!($report instanceof GlobalReport)) {
            // Report tokens only support global reports right now. If you want to support other
            // types, you'll have to update getReportPath to handle it properly.
            throw new Exception('ReportToken only supports global reports.');
        }

        $reportToken = ReportToken::byReport($report)->byOwner($owner)->first();

        if (!$reportToken) {
            $reportToken = ReportToken::create([
                'report_id' => $report->id,
                'report_type' => get_class($report),
                'owner_id' => $owner ? $owner->id : null,
                'owner_type' => $owner ? get_class($owner) : null,
                'expires_at' => $expiresAt ? $expiresAt->toDateString() : null,
                'token' => Util::getRandomString(),
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

        $date = $globalReport->reportingDate->toDateString();

        $reportUrl = '';
        switch ($this->ownerType) {
            case Center::class:
                $center = Center::find($this->ownerId);
                if (!$center) {
                    // Since we don't have a foriegn key constraint on this field, we have to be more careful
                    break;
                }
                $statsReport = $globalReport->getStatsReportByCenter($center);
                if ($statsReport) {
                    $reportUrl = "reports/centers/{$center->abbreviation}/{$date}";
                }
                break;
            case Region::class:
                // Allowing this to fall through.
                // Since we don't have a foriegn key constraint on this field, we have to be more careful
                $region = Region::find($this->ownerId);
            default:
                if (!$region) {
                    $region = App::make(Controller::class)->getRegion(Request::instance());
                }

                $reportUrl = "reports/regions/{$region->abbreviation}/{$date}";
                break;
        }

        return strtolower($reportUrl);
    }

    /**
     * Get the report for this ReportToken
     *
     * @return mixed
     */
    public function getReport()
    {
        $reportClass = $this->reportType;

        return $reportClass::find($this->reportId);
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

    public function scopeByOwner($query, Model $owner = null)
    {
        if ($owner) {
            return $query->whereOwnerId($owner->id)->whereOwnerType(get_class($owner));
        } else {
            return $query->whereNull('owner_id');
        }
    }

    public function scopeByCenter($query, Center $center)
    {
        return $query->whereOwnerId($center->id)->whereOwnerType(get_class($center));
    }

    public function scopeByRegion($query, Region $region)
    {
        return $query->whereOwnerId($region->id)->whereOwnerType(get_class($region));
    }

    public function report()
    {
        return $this->morphTo();
    }

    public function owner()
    {
        return $this->morphTo();
    }
}
