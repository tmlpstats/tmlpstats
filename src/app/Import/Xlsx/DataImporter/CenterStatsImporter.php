<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;
use TmlpStats\Quarter;
use TmlpStats\StatsReport;

use Carbon\Carbon;
use DB;

class CenterStatsImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected $weeks = array();

    protected function populateSheetRanges()
    {
        $this->blocks['classroom1']['cols'] = $this->excelRange('C', 'L');
        $this->blocks['classroom1']['rows'] = $this->excelRange(4, 11);

        $this->blocks['classroom2']['cols'] = $this->excelRange('Q', 'Z');
        $this->blocks['classroom2']['rows'] = $this->excelRange(4, 11);

        $this->blocks['classroom3']['cols'] = $this->excelRange('C', 'L');
        $this->blocks['classroom3']['rows'] = $this->excelRange(14, 21);

        $this->blocks['classroom4']['cols'] = $this->excelRange('Q', 'Z');
        $this->blocks['classroom4']['rows'] = $this->excelRange(14, 21);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock($this->blocks['classroom1']);
        $this->loadBlock($this->blocks['classroom2']);
        $this->loadBlock($this->blocks['classroom3']);
        $this->loadBlock($this->blocks['classroom4']);
    }

    protected function loadBlock($blockParams, $args = null)
    {
        for ($week = 0; $week < count($blockParams['cols']); $week += 2) {
            try {
                $this->loadEntry($week, $blockParams);
            } catch (\Exception $e) {
                $this->addMessage('EXCEPTION_LOADING_ENTRY', $week, $e->getMessage());
            }
        }
    }

    protected function loadEntry($week, $blockParams)
    {
        $promiseCol = $blockParams['cols'][$week];
        $actualCol = $blockParams['cols'][$week + 1];
        $weekCol = $blockParams['cols'][$week];
        $blockStartRow = $blockParams['rows'][0];

        $weekDate = $this->reader->getWeekDate($blockStartRow, $weekCol);

        if (empty($weekDate)) {
            return;
        } else if (is_object($weekDate)) {
            $weekDate = $weekDate->toDateString();
        }

        $promiseData = array(
            'reportingDate' => $weekDate,
            'offset'        => $promiseCol,
            'type'          => 'promise',
            'tdo'           => 100,
        );

        $actualData = array(
            'reportingDate' => $weekDate,
            'offset'        => $actualCol,
            'type'          => 'actual',
            'tdo'           => null,
        );
        $isFutureWeek = $this->statsReport->reportingDate->lt(Carbon::createFromFormat('Y-m-d', $weekDate)->startOfDay());

        $rowHeaders = array('cap', 'cpc', 't1x', 't2x', 'gitw', 'lf');
        for ($i = 0; $i < count($rowHeaders); $i++) {
            $field = $rowHeaders[$i];

            $row = $blockParams['rows'][$i + 2]; // skip 2 title rows

            $promiseValue = $this->reader->getGameValue($row, $promiseCol);
            if ($field == 'gitw') {
                if ($promiseValue <= 1) {
                    $promiseValue = ((int)$promiseValue) * 100;
                } else {
                    $promiseValue = str_replace('%', '', $promiseValue);
                }
            }
            $promiseData[$field] = $promiseValue;

            if ($isFutureWeek) {
                continue;
            }

            $actualValue = $this->reader->getGameValue($row, $actualCol);
            if ($field == 'gitw') {
                if ($actualValue <= 1) {
                    $actualValue = ((int)$actualValue) * 100;
                } else {
                    $actualValue = str_replace('%', '', $actualValue);
                }
            }
            $actualData[$field] = $actualValue;
        }
        $this->data[] = $promiseData;

        if (!$isFutureWeek) {
            $this->data[] = $actualData;
        }
    }

    public function postProcess()
    {
        $isRepromiseWeek = $this->statsReport->reportingDate->eq($this->statsReport->quarter->getClassroom2Date($this->statsReport->center));

        foreach ($this->data as $week) {

            if (isset($this->data['errors'])) {
                continue;
            }

            if ($week['type'] != 'promise') {
                continue;
            }

            $weekDate = Carbon::createFromFormat('Y-m-d', $week['reportingDate'])->startOfDay();
            $promiseData = $this->getPromiseData($weekDate, $this->statsReport->center, $this->statsReport->quarter);

            if (!$promiseData || ($isRepromiseWeek && $weekDate->gt($this->statsReport->reportingDate))) {

                $promiseData = CenterStatsData::firstOrNew(array(
                    'type'            => $week['type'],
                    'reporting_date'  => $week['reportingDate'],
                    'stats_report_id' => $this->statsReport->id,
                ));
                unset($week['offset']);
                unset($week['type']);

                $promiseData = $this->setValues($promiseData, $week);
                $promiseData->points = null;
                $promiseData->save();
            }

            $this->weeks[$week['reportingDate']] = $promiseData;
        }

        foreach ($this->data as $week) {

            if (isset($this->data['errors'])) {
                continue;
            }

            if ($week['type'] != 'actual') {
                continue;
            }

            $weekDate = Carbon::createFromFormat('Y-m-d', $week['reportingDate'])->startOfDay();

            $actualData = $this->getActualData($weekDate, $this->statsReport->center, $this->statsReport->quarter);

            // Always allow setting this week
            if ($actualData && $this->statsReport->reportingDate->ne($weekDate)) {
                continue;
            }

            $promiseData = $this->getPromiseData($weekDate, $this->statsReport->center, $this->statsReport->quarter);

            $actualData = CenterStatsData::firstOrNew(array(
                'type'            => $week['type'],
                'reporting_date'  => $week['reportingDate'],
                'stats_report_id' => $this->statsReport->id,
            ));

            unset($week['offset']);
            unset($week['type']);

            if (!$actualData->exists || $this->statsReport->reportingDate->eq($weekDate)) {

                $actualData = $this->setValues($actualData, $week);
                $actualData->points = $this->calculatePoints($actualData, $promiseData);
                $actualData->save();
            }
        }
    }

    public function getPromiseData(Carbon $date, Center $center, Quarter $quarter)
    {
        // Check cache first
        if (isset($this->weeks[$date->toDateString()])) {
            return $this->weeks[$date->toDateString()];
        }

        $globalReport = null;
        $statsReport = null;

        $firstWeek = $quarter->getFirstWeekDate($center);

        // Don't reuse promises on the first week, as they may change between submits
        if ($this->statsReport->reportingDate->ne($firstWeek)) {

            // Usually, promises will be saved in the global report for the expected week
            $classroom2Date = $quarter->getClassroom2Date($center);
            if ($this->statsReport->reportingDate->gt($classroom2Date) && $date->gt($classroom2Date)) {
                $globalReport = GlobalReport::reportingDate($classroom2Date)->first();
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
        }

        // If we can't find one, or if the only one we could find is from this week
        if (!$statsReport || $statsReport->reportingDate->eq($this->statsReport->reportingDate)) {
            return null;
        }

        return CenterStatsData::promise()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }

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
            ->where('global_reports.reporting_date', '>', $quarter->getQuarterStartDate($center))
            ->where('center_stats_data.type', '=', $type)
            ->orderBy('global_reports.reporting_date', 'ASC')
            ->first();

        if ($statsReportResult) {
            $this->promiseStatsReport = StatsReport::find($statsReportResult->id);
        }

        return $this->promiseStatsReport;
    }

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

        if (!$statsReport || $statsReport->reportingDate->eq($this->statsReport->reportingDate)) {
            return null;
        }

        return CenterStatsData::actual()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }

    public function calculatePoints($actuals, $promises)
    {
        if (!$promises || !$actuals) {
            return null;
        }

        $points = 0;
        $games = array('cap', 'cpc', 't1x', 't2x', 'gitw', 'lf');
        foreach ($games as $game) {

            $promise = $promises->$game;
            $actual = $actuals->$game;
            $percent = StatsReport::calculatePercent($actual, $promise);
            $points += StatsReport::pointsByPercent($percent, $game);
        }
        return $points;
    }
}
