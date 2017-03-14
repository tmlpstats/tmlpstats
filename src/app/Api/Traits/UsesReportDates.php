<?php
namespace TmlpStats\Api\Traits;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Encapsulations;

trait UsesReportDates
{
    protected function lastReportingDate(Models\Center $center, Carbon $reportingDate)
    {
        $cq = Encapsulations\CenterReportingDate::ensure($center, $reportingDate)->getCenterQuarter();

        if ($reportingDate->eq($cq->firstWeekDate)) {
            return null;
        }

        return $reportingDate->copy()->subWeek();
    }

    protected function relevantReport(Models\Center $center, Carbon $reportingDate)
    {
        $cq = Encapsulations\CenterReportingDate::ensure($center, $reportingDate)->getCenterQuarter();

        return Models\StatsReport::byCenter($center)
            ->byQuarter($cq->quarter)
            ->official()
            ->where('reporting_date', '<=', $reportingDate)
            ->orderBy('reporting_date', 'desc')
            ->first();
    }
}
