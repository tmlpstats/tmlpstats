<?php
namespace TmlpStats\Encapsulations;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;

/**
 * A bridge to help prevent repeated quarter lookups
 */
class RegionReportingDate
{
    public $region;
    public $reportingDate;

    protected $quarter = null; // quarter for this region

    public function __construct(Models\Region $region, Carbon $reportingDate, Api\Context $context)
    {
        $this->region = $region;
        $this->reportingDate = $reportingDate;
        if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            throw new Exceptions\BadRequestException('Reporting date must be a Friday.');
        }
        $this->quarter = Models\Quarter::getQuarterByDate($this->reportingDate, $this->region);
    }

    public static function ensure(Models\Region $region, Carbon $reportingDate = null)
    {
        $context = App::make(Api\Context::class);
        if ($reportingDate === null) {
            $reportingDate = $context->getReportingDate();
        }

        return $context->getEncapsulation(self::class, compact('region', 'reportingDate'));
    }

    /**
     * Get the quarter associated with this region-reportingdate
     * @return Models\Quarter
     */
    public function getQuarter()
    {
        return $this->quarter;
    }
}
