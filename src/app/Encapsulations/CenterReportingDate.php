<?php
namespace TmlpStats\Encapsulations;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;

/**
 * This is an entrypoint to caching/memoizing information about a center + reporting date.
 *
 * With these two pieces of information there's a wealth of more information like the quarter, the region,
 * the center quarter, and even the region quarter which we find ourselves often asking for. This abstraction
 * allows us to keep our clean APIs and not pass state around but also not re-select things all the time.
 */
class CenterReportingDate
{
    public $center;
    public $reportingDate;

    protected $context;
    protected $quarter = null; // quarter for this region

    public function __construct(Models\Center $center, Carbon $reportingDate, Api\Context $context)
    {
        $this->context = $context;
        $this->center = $center;
        $this->reportingDate = $reportingDate;
        if ($reportingDate->dayOfWeek !== Carbon::FRIDAY) {
            throw new Exceptions\BadRequestException('Reporting date must be a Friday.');
        }
    }

    public static function ensure(Models\Center $center = null, Carbon $reportingDate = null)
    {
        $context = App::make(Api\Context::class);
        if ($center === null) {
            $center = $context->getCenter();
        }
        if ($reportingDate === null) {
            $reportingDate = $context->getReportingDate();
        }

        return $context->getEncapsulation(self::class, compact('center', 'reportingDate'));
    }

    /**
     * Get the quarter associated with this center-reportingdate
     * @return Models\Quarter
     */
    public function getQuarter()
    {
        if ($this->quarter === null) {
            $this->quarter = RegionReportingDate::ensure($this->center->region, $this->reportingDate)->getQuarter();
        }

        return $this->quarter;
    }

    public function getRegionQuarter()
    {
        return $this->context->getEncapsulation(RegionQuarter::class, [
            'region' => $this->center->region,
            'quarter' => $this->getQuarter(),
        ]);
    }

    public function getCenterQuarter()
    {
        return $this->context->getEncapsulation(Domain\CenterQuarter::class, [
            'center' => $this->center,
            'quarter' => $this->getQuarter(),
        ]);
    }

    public function canShowNextQtrAccountabilities()
    {
        $globalRegion = $this->context->getGlobalRegion();
        // TODO make this configurable in the future, but for this quarter, hard-coded to NA region
        if ($globalRegion === null || $globalRegion->abbreviation != 'NA') {
            return false;
        }
        $cq = $this->getCenterQuarter();
        if ($this->reportingDate->toDateString() == $cq->classroom3Date->toDateString() || $this->reportingDate->gt($cq->classroom3Date)) {
            return true;
        }

        return false;
    }
}
