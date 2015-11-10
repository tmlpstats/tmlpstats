<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\StatsReport;

class RegionByRating extends BaseArrangement
{
    /* Builds a breakdown of all the centers in a region by rating
     * @param $statsReports: an array of stats reports.
     * @return
     *      rows: ordered associative array of Rating => [centers with this rating by points]
     *      summary:
     *         rating, points
     */
    public function build($statsReports)
    {
        // Phase 1: loop all stats reports in this region making a subarray by points.
        $centerPoints = array();
        foreach ($statsReports as $statsReport) {
            $reportPoints = $statsReport->getPoints();
            $centerPoints[$reportPoints][] = $statsReport;
        }
        ksort($centerPoints); // sort by rating points

        // Phase 2: loop the sorted-by-points array and now group by rating.
        $centerReports = array();
        foreach ($centerPoints as $points => $reports) {
            foreach ($reports as $report) {
                $centerReports[$report->getRating()][] = $report;
            }
        }

        return [
            'rows' => $centerReports,
        ];
    }
}
