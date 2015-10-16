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

    protected $blockClassroom1 = array();
    protected $blockClassroom2 = array();
    protected $blockClassroom3 = array();
    protected $blockClassroom4 = array();

    protected function populateSheetRanges()
    {
        $this->blockClassroom1[] = $this->excelRange('C', 'L');
        $this->blockClassroom1[] = $this->excelRange(4, 11);

        $this->blockClassroom2[] = $this->excelRange('Q', 'Z');
        $this->blockClassroom2[] = $this->excelRange(4, 11);

        $this->blockClassroom3[] = $this->excelRange('C', 'L');
        $this->blockClassroom3[] = $this->excelRange(14, 21);

        $this->blockClassroom4[] = $this->excelRange('Q', 'Z');
        $this->blockClassroom4[] = $this->excelRange(14, 21);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock($this->blockClassroom1);
        $this->loadBlock($this->blockClassroom2);
        $this->loadBlock($this->blockClassroom3);
        $this->loadBlock($this->blockClassroom4);
    }

    protected function loadBlock($blockParams, $args = null)
    {
        for ($week = 0; $week < count($blockParams[0]); $week += 2) {
            try {
                $this->loadEntry($week, $blockParams);
            } catch (\Exception $e) {
                $this->addMessage('EXCEPTION_LOADING_ENTRY', $week, $e->getMessage());
            }
        }
    }

    protected function loadEntry($week, $blockParams)
    {
        $promiseCol = $blockParams[0][$week];
        $actualCol = $blockParams[0][$week + 1];
        $weekCol = $blockParams[0][$week];
        $blockStartRow = $blockParams[1][0];

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

            $row = $blockParams[1][$i + 2]; // skip 2 title rows

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
        $isRepromiseWeek = $this->statsReport->reportingDate->eq($this->statsReport->quarter->classroom2Date);

        foreach ($this->data as $week) {

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
                $actualData->points = $this->calculatePoints($promiseData, $actualData);
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

        $firstWeek = clone $quarter->startWeekendDate;
        $firstWeek->addWeek();

        // Don't reuse promises on the first week, as they may change between submits
        if ($this->statsReport->reportingDate->ne($firstWeek)) {

            // Usually, promises will be saved in the global report for the expected week
            if ($this->statsReport->reportingDate->gt($quarter->classroom2Date) && $date->gt($quarter->classroom2Date)) {
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
            ->where('global_reports.reporting_date', '>', $quarter->startWeekendDate)
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

    public function calculatePoints($promises, $actuals)
    {
        if (!$promises || !$actuals) {
            return null;
        }

        $points = 0;
        $games = array('cap', 'cpc', 't1x', 't2x', 'gitw', 'lf');
        foreach ($games as $game) {

            $promise = $promises->$game;
            $actual = $actuals->$game;
            $percent = $promise
                ? round(($actual / $promise) * 100)
                : 0;

            if ($percent >= 100) {
                $gamePoints = 4;
            } else if ($percent >= 90) {
                $gamePoints = 3;
            } else if ($percent >= 80) {
                $gamePoints = 2;
            } else if ($percent >= 75) {
                $gamePoints = 1;
            } else {
                $gamePoints = 0;
            }

            if ($game == 'cap') {
                $gamePoints *= 2;
            }
            $points += $gamePoints;
        }
        return $points;
    }
}
