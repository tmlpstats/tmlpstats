<?php namespace TmlpStats\Reports\Arrangements;

use TmlpStats\StatsReport;

class RegionByRating extends BaseArrangement {

	/* Builds a breakdown of all the centers in a region by rating
	 * @param $statsReports: an array of stats reports.
	 * @return
	 *      rows: ordered associative array of Rating => [centers with this rating by points]
	 *      summary:
	 *         rating, points
	 */
	public function build($statsReports) {
		$totalPoints = 0;

		// Phase 1: loop all stats reports in this region making a subarray by points.
        $centerPoints = array();
        foreach ($statsReports as $statsReport) {

            if (!$statsReport->isValidated()) {
                continue;
            }

            $reportPoints = $statsReport->getPoints();
            $centerPoints[$reportPoints][] = $statsReport;
            $totalPoints += $reportPoints;
        }
        ksort($centerPoints); // sort by rating points

        $points = $centerPoints
            ? round($totalPoints/count($centerPoints))
            : 0;

        // Phase 2: loop the sorted-by-points array and now group by rating.
        $centerReports = array();
        foreach ($centerPoints as $points => $reports) {
            foreach ($reports as $report) {
                $centerReports[$report->getRating()][] = $report;
            }
        }

        return [
            'rows' => $centerReports,
            'summary' => [
            	'rating' => StatsReport::pointsToRating($points), // text rating; e.g. "Ineffective"
            	'points' => $points
            ],
        ];
	}
}
