<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;
use TmlpStats\Util;

use Carbon\Carbon;

class CenterStatsImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected $centerStats = null;
    protected $weeks = array();

    protected static $blockClassroom1 = array();
    protected static $blockClassroom2 = array();
    protected static $blockClassroom3 = array();
    protected static $blockClassroom4 = array();

    protected function populateSheetRanges()
    {
        self::$blockClassroom1[] = $this->excelRange('C','L');
        self::$blockClassroom1[] = $this->excelRange(4,11);

        self::$blockClassroom2[] = $this->excelRange('Q','Z');
        self::$blockClassroom2[] = $this->excelRange(4,11);

        self::$blockClassroom3[] = $this->excelRange('C','L');
        self::$blockClassroom3[] = $this->excelRange(14,21);

        self::$blockClassroom4[] = $this->excelRange('Q','Z');
        self::$blockClassroom4[] = $this->excelRange(14,21);
    }

    public function getCenterStats()
    {
        return $this->centerStats;
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock(self::$blockClassroom1);
        $this->loadBlock(self::$blockClassroom2);
        $this->loadBlock(self::$blockClassroom3);
        $this->loadBlock(self::$blockClassroom4);
    }

    protected function loadBlock($blockParams, $args = null)
    {
        for ($week = 0; $week < count($blockParams[0]); $week+=2) {
            try{
                $this->loadEntry($week, $blockParams);
            } catch(\Exception $e) {
                $this->addMessage('EXCEPTION_LOADING_ENTRY', $week, $e->getMessage());
            }
        }
    }

    protected function loadEntry($week, $blockParams)
    {
        $promiseCol    = $blockParams[0][$week];
        $actualCol     = $blockParams[0][$week+1];
        $weekCol       = $blockParams[0][$week];
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
        $hasActuals = true;

        $rowHeaders = array('cap', 'cpc', 't1x', 't2x', 'gitw', 'lf');
        for ($i = 0; $i < count($rowHeaders); $i++) {
            $field = $rowHeaders[$i];

            $row = $blockParams[1][$i+2]; // skip 2 title rows

            $promiseValue = $this->reader->getGameValue($row, $promiseCol);
            if ($field == 'gitw') {
                if ($promiseValue <= 1) {
                    $promiseValue = ((int)$promiseValue) * 100;
                } else {
                    $promiseValue = str_replace('%', '', $promiseValue);
                }
            }
            $promiseData[$field] = $promiseValue;

            $actualValue = $this->reader->getGameValue($row, $actualCol);

            if (!$hasActuals || $actualValue === null) {
                $hasActuals = false;
                continue;
            }

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

        if ($hasActuals) {
            $this->data[] = $actualData;
        }
    }

    public function postProcess()
    {
        $isSecondClassroom = $this->statsReport->reportingDate->eq($this->statsReport->quarter->classroom2Date);

        // Calculate Rating
        foreach ($this->data as $week) {

            $weekDate = Carbon::createFromFormat('Y-m-d', $week['reportingDate']);

            $centerStats = CenterStats::firstOrNew(array(
                'center_id'      => $this->statsReport->center->id,
                'quarter_id'     => $this->statsReport->quarter->id,
                'reporting_date' => $week['reportingDate'],
            ));

            if ($week['type'] == 'promise') {
                $promiseData = CenterStatsData::firstOrNew(array(
                    'center_id'      => $this->statsReport->center->id,
                    'quarter_id'     => $this->statsReport->quarter->id,
                    'reporting_date' => $week['reportingDate'],
                    'type'           => $week['type'],
                ));

                unset($week['type']);

                if (!$promiseData->exists || ($isSecondClassroom && $weekDate->gt($this->statsReport->reportingDate))) {
                    $promiseData = $this->setValues($promiseData, $week);

                    if (!$promiseData->exists) {
                        $promiseData->statsReportId = $this->statsReport->id;
                        $promiseData->save();

                        $centerStats->promiseDataId = $promiseData->id;
                    } else if ($promiseData->isDirty()) {
                        $newPromiseData = $promiseData->replicate();

                        $newPromiseData->statsReportId = $this->statsReport->id;
                        $newPromiseData->save();

                        $centerStats->revokedPromiseDataId = $promiseData->id;
                        $centerStats->promiseDataId = $newPromiseData->id;
                    }

                }
            } else if ($week['type'] == 'actual') {

                $actualData = CenterStatsData::firstOrNew(array(
                    'center_id'      => $this->statsReport->center->id,
                    'quarter_id'     => $this->statsReport->quarter->id,
                    'reporting_date' => $week['reportingDate'],
                    'type'           => $week['type'],
                ));

                unset($week['type']);

                if (!$actualData->exists || $this->statsReport->reportingDate->eq($weekDate)) {

                    $actualData = $this->setValues($actualData, $week);

                    $points = $this->calculatePoints($promiseData, $actualData);
                    $rating = $this->getRating($points);

                    $actualData->rating = "$rating ($points)";
                    $actualData->statsReportId = $this->statsReport->id;
                    $actualData->save();

                    $centerStats->actualDataId = $actualData->id;
                }
            }

            if ($centerStats->isDirty()) {
                $centerStats->statsReportId = $this->statsReport->id;
                $centerStats->save();
            }

            if ($weekDate->eq($this->statsReport->reportingDate)) {
                $this->centerStats = $centerStats;
            }
        }
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

    public function getRating($points)
    {
        if ($points == 28) {
            return "Powerful";
        } else if ($points >= 22) {
            return "High Performing";
        } else if ($points >= 16) {
            return "Effective";
        } else if ($points >= 9) {
            return "Marginally Effective";
        } else {
            return "Ineffective";
        }
    }
}
