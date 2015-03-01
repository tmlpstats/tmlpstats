<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;

use Carbon\Carbon;

class CenterStatsImporter extends DataImporterAbstract
{
    protected $classDisplayName = "Weekly Center Stats";

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
            $this->loadEntry($week, $blockParams);
        }
    }

    protected function loadEntry($week, $blockParams)
    {
        $promiseCol    = $blockParams[0][$week];
        $actualCol     = $blockParams[0][$week+1];
        $weekCol       = $blockParams[0][$week];
        $blockStartRow = $blockParams[1][0];

        $weekDate = $this->reader->getWeekDate($blockStartRow, $weekCol);

        if ($weekDate === false) {
            $this->addMessage("Week end date in column $weekCol is not in the correct format. The sheet may be corrupt.", 'error', $row);
        } else if (!$weekDate) {
            // There was no week date found. skip this column since it's blank
            return;
        }

        $centerStats = CenterStats::firstOrCreate(array(
            'center_id'      => $this->statsReport->center->id,
            'quarter_id'     => $this->statsReport->quarter->id,
            'reporting_date' => $weekDate->toDateString(),
        ));

        $isSecondClassroom = $this->statsReport->reportingDate->eq($this->statsReport->quarter->classroom2Date);
        $hasRepromise = false;

        $promiseData = null;
        $newPromiseData = null;
        if ($centerStats->promiseDataId) {

            if ($centerStats->actualDataId) {
                return; // Week already populated. Nothing to do here...
            }

            $promiseData = CenterStatsData::find($centerStats->promiseDataId);
        }

        // Otherwise, this is the first week, and we need to import all of the promises
        if ($promiseData == null || ($isSecondClassroom && $weekDate->gt($this->statsReport->reportingDate))) {
            // Have to use create here to get a brand new one. Otherwise, it will use the old version.
            $newPromiseData = CenterStatsData::create(array(
                'center_id'      => $this->statsReport->center->id,
                'quarter_id'     => $this->statsReport->quarter->id,
                'reporting_date' => $weekDate->toDateString(),
                'type'           => 'promise',
                'offset'         => $promiseCol
            ));
            $newPromiseData->tdo = 100;
        }

        $actualData = null;
        // Only create actualData if this is the current week or a past week with no actual data
        if ($weekDate->lte($this->statsReport->reportingDate)) {
            $actualData = CenterStatsData::firstOrCreate(array(
                'center_id'      => $this->statsReport->center->id,
                'quarter_id'     => $this->statsReport->quarter->id,
                'reporting_date' => $weekDate->toDateString(),
                'type'           => 'actual',
                'offset'         => $actualCol,
            ));
        }

        $rowHeaders = array('cap', 'cpc', 't1x', 't2x', 'gitw', 'lf');
        for ($i = 0; $i < count($rowHeaders); $i++) {
            $field = $rowHeaders[$i];

            $row = $blockParams[1][$i+2]; // skip 2 title rows

            // Only set data if this is a new promise object (no updates)
            if ($newPromiseData) {
                $value = (string)$this->reader->getGameValue($row, $promiseCol);
                if ($field == 'gitw') {
                    if ($value <= 1) {
                        $value = ((int)$value) * 100;
                    } else {
                        $value = str_replace('%', '', $value);
                    }
                }
                if ($promiseData && $promiseData->$field != $value) {
                    $hasRepromise = true;
                }
                $newPromiseData->$field = $value;
            }

            if ($actualData) {
                $value = (string)$this->reader->getGameValue($row, $actualCol);
                if ($field == 'gitw') {
                    if ($value <= 1) {
                        $value = ((int)$value) * 100;
                    } else {
                        $value = str_replace('%', '', $value);
                    }
                }
                $actualData->$field = $value;
            }
        }

        if ($newPromiseData) {
            $newPromiseData->statsReportId = $this->statsReport->id;
            $newPromiseData->save();

            $centerStats->promiseDataId = $newPromiseData->id;

            if ($isSecondClassroom && $hasRepromise) {
                $centerStats->revokedPromiseDataId = $promiseData->id;
                $centerStats->promiseDataId = $newPromiseData->id;

                $promiseData = null; // make sure we use the right value for post processing
            }
        }

        // Only save new actuals.
        if ($actualData) {
            $actualData->statsReportId = $this->statsReport->id;
            $actualData->save();

            $centerStats->actualDataId = $actualData->id;

            // Save new weeks for post processing
            $week = array();
            $week['promises'] = $promiseData ?: $newPromiseData;
            $week['actuals'] = $actualData;
            $this->weeks[] = $week;
        }
        if ($centerStats->statsReportId === null) {
            $centerStats->statsReportId = $this->statsReport->id;
        }
        $centerStats->save();

        if ($weekDate->eq($this->statsReport->reportingDate)) {
            $this->centerStats = $centerStats;
        }
    }

    public function postProcess()
    {
        foreach ($this->weeks as $week) {

            if (!isset($week['promises']) || !isset($week['actuals'])) {
                continue;
            }
            $actualData = $week['actuals'];
            $promiseData = $week['promises'];

            $points = 0;
            $games = array('cap', 'cpc', 't1x', 't2x', 'gitw', 'lf');
            foreach ($games as $game) {

                $promise = $promiseData->$game;
                $actual = $actualData->$game;
                $percent = $promise
                    ? ($actual / $promise) * 100
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

            if ($points == 28) {
                $rating = 'Powerful';
            } else if ($points >= 22) {
                $rating = 'High Performing';
            } else if ($points >= 16) {
                $rating = 'Effective';
            } else if ($points >= 9) {
                $rating = 'Marginally Effective';
            } else {
                $rating = 'Ineffective';
            }
            $actualData->rating = $rating;
            $actualData->save();
        }
    }
}
