<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\TmlpGame;
use TmlpStats\TmlpGameData;

class TmlpGameInfoImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_COURSES;

    protected static $blockT1X = array();
    protected static $blockT2X = array();

    protected function populateSheetRanges()
    {
        self::$blockT1X[] = $this->excelRange('A','K');
        self::$blockT1X[] = $this->excelRange(30,31);

        self::$blockT2X[] = $this->excelRange('A','K');
        self::$blockT2X[] = $this->excelRange(38,39);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock(self::$blockT1X, 'T1X');
        $this->loadBlock(self::$blockT2X, 'T2X');
    }

    protected function loadEntry($row, $type)
    {
        if ($this->reader->isEmptyCell($row,'B')) return;

        $this->data[] = array(
            'offset'                 => $row,
            'type'                   => $this->reader->getType($row),
            'quarterStartRegistered' => $this->reader->getQuarterStartRegistered($row),
            'quarterStartApproved'   => $this->reader->getQuarterStartApproved($row),
        );
    }

    public function postProcess()
    {
        foreach ($this->data as $gameInput) {

            $game = TmlpGame::firstOrNew(array(
                'center_id' => $this->statsReport->center->id,
                'type'      => $gameInput['type'],
            ));
            if ($game->statsReportId == null) {
                $game->statsReportId = $this->statsReport->id;
                $game->save();
            }

            $gameData = TmlpGameData::firstOrNew(array(
                'center_id'      => $this->statsReport->center->id,
                'quarter_id'     => $this->statsReport->quarter->id,
                'tmlp_game_id'   => $game->id,
                'reporting_date' => $this->statsReport->reportingDate->toDateString(),
            ));

            unset($gameInput['type']);
            $this->setValues($gameData, $gameInput);

            $gameData->statsReportId = $this->statsReport->id;
            $gameData->save();
        }
    }
}
