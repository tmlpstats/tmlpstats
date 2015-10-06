<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;
use TmlpStats\Quarter;

use Carbon\Carbon;

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

        // Calculate Rating
        foreach ($this->data as $week) {

            $weekDate = Carbon::createFromFormat('Y-m-d', $week['reportingDate'])->startOfDay();
            $promiseData = $this->getPromiseData($weekDate, $this->statsReport->center, $this->statsReport->quarter);

            if ($week['type'] == 'promise') {
                if (!$promiseData || ($isRepromiseWeek && $weekDate->gt($this->statsReport->reportingDate))) {

                    $promiseData = CenterStatsData::firstOrNew(array(
                        'type'            => $week['type'],
                        'reporting_date'  => $week['reportingDate'],
                        'stats_report_id' => $this->statsReport->id,
                    ));
                    unset($week['offset']);
                    unset($week['type']);

                    $promiseData = $this->setValues($promiseData, $week);
                    $promiseData->save();
                }

            } else if ($week['type'] == 'actual') {

                $actualData = $this->getActualData($weekDate, $this->statsReport->center);
                if ($actualData) {
                    continue;
                }

                $actualData = CenterStatsData::firstOrNew(array(
                    'type'            => $week['type'],
                    'reporting_date'  => $week['reportingDate'],
                    'stats_report_id' => $this->statsReport->id,
                ));

                unset($week['offset']);
                unset($week['type']);

                if ($this->statsReport->reportingDate->eq($weekDate)) {

                    $actualData = $this->setValues($actualData, $week);
                    $actualData->points = $this->calculatePoints($promiseData, $actualData);
                    $actualData->save();
                }
            }
        }
    }

    public function getPromiseData(Carbon $date, Center $center, Quarter $quarter = null)
    {
        if (!$quarter) {
            $quarter = Quarter::byRegion($center->region)
                ->date($date)
                ->first();

            $quarter->setRegion($center->region);
        }

        if ($date->gt($quarter->classroom2Date)) {
            $globalReport = GlobalReport::reportingDate($quarter->classroom2Date)->first();
        } else {
            $globalReport = GlobalReport::reportingDate($quarter->startWeekendDate->addWeek())->first();
        }

        if (!$globalReport) {
            return null;
        }

        $statsReport = $globalReport->statsReports()->byCenter($center)->first();
        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::promise()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }

    public function getActualData(Carbon $date, Center $center)
    {
        $globalReport = GlobalReport::reportingDate($date)->first();
        if (!$globalReport) {
            return null;
        }

        $statsReport = $globalReport->statsReports()->byCenter($center)->first();
        if (!$statsReport) {
            return null;
        }

        return CenterStatsData::actual()
            ->reportingDate($date)
            ->byStatsReport($statsReport)
            ->first();
    }

    public function calculatePoints($promises, $actuals)
    {
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
