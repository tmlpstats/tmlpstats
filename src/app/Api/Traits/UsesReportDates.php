<?php
namespace TmlpStats\Api\Traits;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Encapsulations;

trait UsesReportDates
{
    protected $_crd = null;

    protected function lastReportingDate(Models\Center $center, Carbon $reportingDate)
    {
        $quarter = $this->getCenterReportingDate($center, $reportingDate)->getQuarter();

        if ($reportingDate->eq($quarter->getFirstWeekDate())) {
            return null;
        }

        return $reportingDate->copy()->subWeek();
    }

    protected function relevantReport(Models\Center $center, Carbon $reportingDate)
    {
        $quarter = $this->getCenterReportingDate($center, $reportingDate)->getQuarter();

        return Models\StatsReport::byCenter($center)
            ->byQuarter($quarter)
            ->official()
            ->where('reporting_date', '<=', $reportingDate)
            ->orderBy('reporting_date', 'desc')
            ->first();
    }

    private function getCenterReportingDate(Models\Center $center, Carbon $reportingDate)
    {
        if (!$this->_crd) {
            $this->_crd = Encapsulations\CenterReportingDate::ensure($center, $reportingDate);
        }

        return $this->_crd;
    }
}
