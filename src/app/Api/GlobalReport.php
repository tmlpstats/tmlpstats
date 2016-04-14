<?php namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple methods
// that take typed input and return array responses.
use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Reports\Arrangements;

class GlobalReport
{
    public function getRating(Models\GlobalReport $report)
    {
        return []; // TODO
    }

    public function getQuarterScoreboard(Models\GlobalReport $report, Models\Region $region)
    {
        $statsReports = $report->statsReports()
                               ->validated()
                               ->byRegion($region)
                               ->get();

        $cumulativeData = [];
        foreach ($statsReports as $statsReport) {
            $centerStatsData = App::make(LocalReport::class)->getQuarterScoreboard($statsReport);

            foreach ($centerStatsData as $dateStr => $week) {
                foreach (['promise', 'actual'] as $type) {

                    // Skip if we don't have that type, or if the data is empty
                    if (!isset($week[$type]) || $week[$type]['cap'] === null) {
                        continue;
                    }

                    $data = $week[$type];

                    if (isset($cumulativeData[$dateStr][$type])) {
                        $weekData = $cumulativeData[$dateStr][$type];
                    } else {
                        $weekData = new \stdClass();
                        $weekData->type = $type;
                        $weekData->reportingDate = Carbon::parse($dateStr);
                    }

                    foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game) {

                        if (!isset($weekData->$game)) {
                            $weekData->$game = 0;
                        }
                        $weekData->$game += $data[$game];
                    }
                    $cumulativeData[$dateStr][$type] = $weekData;
                }
            }
        }

        $scoreboardData = [];
        $count = count($statsReports);
        foreach ($cumulativeData as $date => $week) {
            foreach ($week as $type => $data) {
                // GITW is calculated as an average, so we need the total first
                $total = $data->gitw;
                $data->gitw = ($total / $count);

                $scoreboardData[] = $data;
            }
        }

        $a = new Arrangements\GamesByWeek($scoreboardData);
        $weeklyData = $a->compose();

        return $weeklyData['reportData'];
    }

    public function getWeekScoreboard(Models\GlobalReport $report, Models\Region $region)
    {
        $scoreboardData = $this->getQuarterScoreboard($report, $region);

        $dateStr = $report->reportingDate->toDateString();
        if (isset($scoreboardData[$dateStr])) {
            return $scoreboardData[$dateStr];
        }

        return [];
    }

    public function getWeekScoreboardByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $date = $report->reportingDate;
        if (isset($options['date']) && ($options['date'] instanceof Carbon || is_string($options['date']))) {
            $date = is_string($options['date']) ? Carbon::parse($options['date']) : $options['date'];
        }

        $includeOriginalPromise = isset($options['includeOriginalPromise']) ? (bool) $options['includeOriginalPromise'] : false;

        $statsReports = $report->statsReports()
                               ->validated()
                               ->byRegion($region)
                               ->get();

        $dateStr = $date->toDateString();

        $reportData = [];
        foreach ($statsReports as $statsReport) {
            $centerStatsData = App::make(LocalReport::class)->getQuarterScoreboard($statsReport, compact('includeOriginalPromise'));

            $centerName = $statsReport->center->name;

            $reportData[$centerName] = $centerStatsData[$dateStr];
        }

        return $reportData;
    }

    public function getApplicationsListByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;

        $statsReports = $report->statsReports()
            ->byRegion($region)
            ->reportingDate($report->reportingDate)
            ->get();

        $registrations = [];
        foreach ($statsReports as $statsReport) {

            $reportRegistrations = App::make(LocalReport::class)->getApplicationsList($statsReport, [
                'returnUnprocessed' => $returnUnprocessed,
            ]);

            foreach ($reportRegistrations as $registration) {
                $registrations[] = $registration;
            }
        }

        return $registrations;
    }
}
