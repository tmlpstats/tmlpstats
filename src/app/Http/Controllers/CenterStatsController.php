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

    public function getByGlobalReport($id)
    {
        $cacheKey = "globalreport{$id}:centerstats";
        $globalReportData = Cache::get($cacheKey);

        if (!$globalReportData) {

            $globalReport = GlobalReport::find($id);

            if (!$globalReport) {
                return null;
            }

            $statsReports = $globalReport->statsReports()->get();

            $count = count($statsReports);
            $globalReportData = [];
            foreach ($statsReports as $statsReport) {
                $centerStatsData = $this->getByStatsReport($statsReport->id);
                foreach ($centerStatsData as $section => $sectionData) {
                    foreach ($sectionData as $date => $week) {
                        foreach ($week as $type => $data) {
                            foreach (['cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'] as $game) {
                                if (!isset($globalReportData[$section][$date][$type][$game])) {
                                    $globalReportData[$section][$date][$type][$game] = 0;
                                }
                                $globalReportData[$section][$date][$type][$game] += $data->$game;
                            }
                        }
                    }
                }
            }
            foreach ($globalReportData as $section => $sectionData) {
                foreach ($sectionData as $date => $week) {
                    foreach ($week as $type => $data) {
                        $val = $data['gitw'];
                        $globalReportData[$section][$date][$type]['gitw'] = round($val/$count);
                    }
                }
            }
        }
        Cache::tags(["globalReport{$id}"])->put($cacheKey, $globalReportData, static::CACHE_TTL);

        return $globalReportData;
    }

    protected $statsReport = null;

    public function getByStatsReport($id)
    {
        $cacheKey = "statsReport{$id}:centerstats";
        $centerStatsData = Cache::get($cacheKey);

        if (!$centerStatsData) {
            $statsReport = StatsReport::find($id);

            $this->statsReport = $statsReport;

            if (!$statsReport) {
                return null;
            }

            // Center Stats
            $week = clone $statsReport->quarter->startWeekendDate;
            $week->addWeek();
            $centerStatsData = array();
            while ($week->lte($statsReport->quarter->endWeekendDate)) {

                if ($week->lte($statsReport->quarter->classroom1Date)) {
                    $classroom = 0;
                } else if ($week->lte($statsReport->quarter->classroom2Date)) {
                    $classroom = 1;
                } else if ($week->lte($statsReport->quarter->classroom3Date)) {
                    $classroom = 2;
                } else {
                    $classroom = 3;
                }

                $centerStatsData[$classroom][$week->toDateString()]['promise'] = $this->getPromiseData($week, $statsReport->center, $statsReport->quarter);

                if ($week->lte($statsReport->reportingDate)) {
                    $centerStatsData[$classroom][$week->toDateString()]['actual'] = $this->getActualData($week, $statsReport->center, $statsReport->quarter);
                }

                $week->addWeek();
            }
        }
        Cache::tags(["statsReport{$id}"])->put($cacheKey, $centerStatsData, static::STATS_REPORT_CACHE_TTL);

        return $centerStatsData;
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
            $globalReport = GlobalReport::reportingDate($quarter->classroom2Date)->first();
        } else {
            $globalReport = GlobalReport::reportingDate($firstWeek)->first();
        }

        // If there was a global report from those weeks, look there
        if ($globalReport) {
            $statsReport = $globalReport->statsReports()->byCenter($center)->first();
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
    protected $promiseStatsReport = null;

    public function findFirstWeek(Center $center, Quarter $quarter, $type)
    {
        // Promises should all be saved during the same week. Let's remember where we found the
        // last one.
        if ($this->promiseStatsReport) {
            return $this->promiseStatsReport;
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
            $this->promiseStatsReport = StatsReport::find($statsReportResult->id);
        }

        return $this->promiseStatsReport;
    }

    // TODO: Refactor this so we're not reusing basically the same code as in importer
    public function getActualData(Carbon $date, Center $center, Quarter $quarter)
    {
        $statsReport = null;

        // First, check if it's in the official report from the actual date
        $globalReport = GlobalReport::reportingDate($date)->first();
        if ($globalReport) {
            $statsReport = $globalReport->statsReports()->byCenter($center)->first();
        }

        // If not, search from the beginning until we find it
        if (!$statsReport) {
            $statsReport = $this->findFirstWeek($center, $quarter, 'actual');
        }

        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::actual()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }
}
