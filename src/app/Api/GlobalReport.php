<?php namespace TmlpStats\Api;

// This is an API servicer. All API methods are simple methods
// that take typed input and return array responses.
use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\ApiBase;
use TmlpStats\Reports\Arrangements;

class GlobalReport extends ApiBase
{
    public function getRating(Models\GlobalReport $report, Models\Region $region)
    {
        $cacheObjs = compact('report', 'region');
        $cached = $this->checkCache($cacheObjs);
        if ($cached) {
            return $cached;
        }

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return null;
        }

        $weeklyData = $this->getQuarterScoreboard($report, $region);

        $a = new Arrangements\RegionByRating($statsReports);
        $data = $a->compose();

        $dateString = $report->reportingDate->toDateString();
        $data['summary']['points'] = $weeklyData[$dateString]['points']['total'];
        $data['summary']['rating'] = $weeklyData[$dateString]['rating'];

        $this->putCache($cacheObjs, $data);

        return $data;
    }

    public function getQuarterScoreboard(Models\GlobalReport $report, Models\Region $region)
    {
        $cacheObjs = compact('report', 'region');
        $cached = $this->checkCache($cacheObjs);
        if ($cached) {
            return $cached;
        }

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return [];
        }

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

        $this->putCache($cacheObjs, $weeklyData['reportData']);

        return $weeklyData['reportData'];
    }

    public function getWeekScoreboard(Models\GlobalReport $report, Models\Region $region, Carbon $futureDate = null)
    {
        $cacheObjs = compact('report', 'region');
        $cached = $this->checkCache($cacheObjs);
        if ($cached) {
            return $cached;
        }

        $scoreboardData = $this->getQuarterScoreboard($report, $region);

        $dateStr = $futureDate ? $futureDate->toDateString() : $report->reportingDate->toDateString();

        $reportData = [];
        if (isset($scoreboardData[$dateStr])) {
            $reportData = $scoreboardData[$dateStr];
        }

        $this->putCache($cacheObjs, $reportData);

        return $reportData;
    }

    public function getWeekScoreboardByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $cacheObjs = $this->merge(compact('report', 'region'), $options);
        $cached = $this->checkCache($cacheObjs);
        if ($cached) {
            return $cached;
        }

        $date = $report->reportingDate;
        if (isset($options['date']) && ($options['date'] instanceof Carbon || is_string($options['date']))) {
            $date = is_string($options['date']) ? Carbon::parse($options['date']) : $options['date'];
        }

        $includeOriginalPromise = isset($options['includeOriginalPromise']) ? (bool) $options['includeOriginalPromise'] : false;

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return [];
        }

        $dateStr = $date->toDateString();

        $reportData = [];
        foreach ($statsReports as $statsReport) {
            $centerStatsData = App::make(LocalReport::class)->getQuarterScoreboard($statsReport, compact('includeOriginalPromise'));

            $centerName = $statsReport->center->name;

            $reportData[$centerName] = $centerStatsData[$dateStr];
        }

        $this->putCache($cacheObjs, $reportData);

        return $reportData;
    }

    public function getApplicationsListByCenter(Models\GlobalReport $report, Models\Region $region, $options = [])
    {
        $cacheObjs = $this->merge(compact('report', 'region'), $options);
        $cached = $this->checkCache($cacheObjs);
        if ($cached) {
            return $cached;
        }

        $returnUnprocessed = isset($options['returnUnprocessed']) ? (bool) $options['returnUnprocessed'] : false;

        $statsReports = $this->getStatsReports($report, $region);
        if ($statsReports->isEmpty()) {
            return [];
        }

        $registrations = [];
        foreach ($statsReports as $statsReport) {

            $reportRegistrations = App::make(LocalReport::class)->getApplicationsList($statsReport, [
                'returnUnprocessed' => $returnUnprocessed,
            ]);

            foreach ($reportRegistrations as $registration) {
                $registrations[] = $registration;
            }
        }

        $this->putCache($cacheObjs, $registrations);

        return $registrations;
    }

    protected function getStatsReports(Models\GlobalReport $report, Models\Region $region)
    {
        return $report->statsReports()
                      ->validated()
                      ->byRegion($region)
                      ->get();
    }
}
