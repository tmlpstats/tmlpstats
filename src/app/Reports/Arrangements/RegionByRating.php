<?php
namespace TmlpStats\Reports\Arrangements;

use TmlpStats\StatsReport;

class RegionByRating extends BaseArrangement
{
    /* Builds a breakdown of all the centers in a region by rating
     * @param $centerData: an array of scoreboards (keyed by center)
     * @return
     *      rows: ordered associative array of Rating => [centers with this rating by points]
     *      summary:
     *         rating, points
     */
    public function build($centerData)
    {
        // Phase 1: loop all stats reports in this region making a subarray by points.
        $centerPoints = array();
        foreach ($centerData as $center => $sb) {
            // Skip any centers that don't have stats for this week
            if ($sb->game('cap')->actual() === null) {
                continue;
            }

            $points = $sb->points();
            $centerPoints[$points][] = compact('center', 'points');
        }
        ksort($centerPoints); // sort by rating points

        // Phase 2: loop the sorted-by-points array and now group by rating.
        $centerReports = array();
        foreach ($centerPoints as $points => $reports) {
            foreach ($reports as $report) {
                $sb = $centerData[$report['center']];
                $centerReports[$sb->rating()][] = $report;
            }
        }

        return [
            'rows' => $centerReports,
        ];
    }
}
