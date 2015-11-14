<?php

namespace TmlpStats\Http\Controllers;

use Cache;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use TmlpStats\Center;
use TmlpStats\CenterStatsData;
use TmlpStats\GlobalReport;
use TmlpStats\Http\Requests;
use TmlpStats\Quarter;
use TmlpStats\Region;
use TmlpStats\StatsReport;

class CenterStatsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getByGlobalReport(GlobalReport $globalReport, Region $region = null, Carbon $onlyThisDate = null)
    {
        $statsReports = $globalReport->statsReports()
            ->validated()
            ->byRegion($region)
            ->get();

        $cumulativeData = [];
        foreach ($statsReports as $statsReport) {
            $centerStatsData = $this->getByStatsReport($statsReport, $onlyThisDate);

            foreach ($centerStatsData as $week) {
                $dateString = $week->reportingDate->toDateString();
                $type = $week->type;

                $weekData = isset($cumulativeData[$dateString][$type])
                    ? $cumulativeData[$dateString][$type]
                    : new \stdClass();

                $weekData->type = $type;
                $weekData->reportingDate = $week->reportingDate;

                foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game) {
                    if (!isset($weekData->$game)) {
                        $weekData->$game = 0;
                    }
                    $weekData->$game += $week[$game];
                }
                $cumulativeData[$dateString][$type] = $weekData;
            }
        }

        $globalReportData = [];
        $count = count($statsReports);
        foreach ($cumulativeData as $date => $week) {
            foreach ($week as $type => $data) {
                // GITW is calculated as an average, so we need the total first
                $total = $data->gitw;
                $data->gitw = round($total / $count);

                $globalReportData[] = $data;
            }
        }

        return $globalReportData;
    }

    protected $statsReport = null;

    public function getByStatsReport(StatsReport $statsReport, Carbon $onlyThisDate = null)
    {
        $this->statsReport = $statsReport;

        $centerStatsData = [];
        if ($onlyThisDate) {
            // Get only the data for the requested date
            $centerStatsData = $this->getWeekData($onlyThisDate, $statsReport->center, $statsReport->quarter);
        } else {
            // Get all weeks for stats report
            $week = clone $statsReport->quarter->startWeekendDate;
            $week->addWeek();
            while ($week->lte($statsReport->quarter->endWeekendDate)) {

                $weekData = $this->getWeekData(
                    $week,
                    $statsReport->center,
                    $statsReport->quarter,
                    $week->gt($statsReport->reportingDate)
                );
                $centerStatsData = array_merge($centerStatsData, $weekData);

                $week->addWeek();
            }
        }

        return $centerStatsData;
    }

    public function getWeekData(Carbon $date, Center $center, Quarter $quarter, $excludeActual = false)
    {
        $output = [];
        $output[] = $this->getPromiseData($date, $center, $quarter);
        if (!$excludeActual) {
            $output[] = $this->getActualData($date, $center, $quarter);
        }

        return $output;
    }

    protected $globalReportCache = [];

    /**
     * Get global report by date, and cache it for later
     *
     * @param Carbon $reportingDate
     * @return mixed
     */
    protected function getGlobalReport(Carbon $reportingDate)
    {
        $date = $reportingDate->toDateString();
        if (!isset($this->globalReportCache[$date])) {
            $this->globalReportCache[$date] = GlobalReport::reportingDate($reportingDate)->with('statsReports')->first();
        }
        return $this->globalReportCache[$date];
    }


    // TODO: Refactor this so we're not reusing basically the same code as in importer
    public function getPromiseData(Carbon $date, Center $center, Quarter $quarter)
    {
        $globalReport = null;
        $statsReport = null;

        $firstWeek = clone $quarter->startWeekendDate;
        $firstWeek->addWeek();

        // Usually, promises will be saved in the global report for the expected week
        if ($this->statsReport->reportingDate->gte($quarter->classroom2Date) && $date->gt($quarter->classroom2Date)) {
            $globalReport = $this->getGlobalReport($quarter->classroom2Date);
        } else {
            $globalReport = $this->getGlobalReport($firstWeek);
        }

        // If there was a global report from those weeks, look there
        if ($globalReport) {
            $statsReport = $globalReport->getStatsReportByCenter($center);
        }

        // It it wasn't found in the expected week, search all weeks from the beginning until
        // we find it
        if (!$statsReport) {
            $statsReport = $this->findFirstWeek($center, $quarter, 'promise');
        }

        // If we can't find one, or if the only one we could find is from this week
        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::promise()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }

    // TODO: Refactor this so we're not reusing basically the same code as in importer
    public function getActualData(Carbon $date, Center $center, Quarter $quarter)
    {
        $statsReport = null;

        // First, check if it's in the official report from the actual date
        $globalReport = $this->getGlobalReport($date);
        if ($globalReport) {
            $statsReport = $globalReport->getStatsReportByCenter($center);
        }

        $actual = null;
        if ($statsReport) {
            $actual = CenterStatsData::actual()
                ->reportingDate($date)
                ->byStatsReport($statsReport)
                ->first();
        }

        // If not, search from the beginning until we find it
        if (!$actual) {
            $statsReport = $this->findFirstWeek($center, $quarter, 'actual');
        } else {
            return $actual;
        }

        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::actual()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }


    protected $firstStatsReportCache = [];
    public function findFirstWeek(Center $center, Quarter $quarter, $type)
    {
        // Promises should all be saved during the same week. Let's remember where we found the
        // last one.
        if (isset($this->firstStatsReportCache[$center->id])) {
            return $this->firstStatsReportCache[$center->id];
        }

        $statsReportResult = DB::table('stats_reports')
            ->select('stats_reports.id')
            ->join('center_stats_data', 'center_stats_data.stats_report_id', '=', 'stats_reports.id')
            ->join('global_report_stats_report', 'global_report_stats_report.stats_report_id', '=', 'stats_reports.id')
            ->join('global_reports', 'global_reports.id', '=', 'global_report_stats_report.global_report_id')
            ->where('stats_reports.center_id', '=', $center->id)
            ->where('global_reports.reporting_date', '>', $quarter->startWeekendDate)
            ->where('center_stats_data.type', '=', $type)
            ->orderBy('global_reports.reporting_date', 'ASC')
            ->first();

        if ($statsReportResult) {
            $this->firstStatsReportCache[$center->id] = StatsReport::find($statsReportResult->id);
        }

        return $this->firstStatsReportCache[$center->id];
    }
}
