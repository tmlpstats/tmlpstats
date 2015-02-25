<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\TmlpGame;
use TmlpStats\TmlpGameData;

class TmlpGameInfoImporter extends DataImporterAbstract
{
    protected $classDisplayName = "CAP & CPC Course Info";

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

        $game = TmlpGame::firstOrCreate(array(
            'center_id' => $this->statsReport->center->id,
            'type'      => $this->reader->getType($row),
        ));
        if ($game->statsReportId == null) {
            $game->statsReportId = $this->statsReport->id;
        }
        $game->save();

        $gameData = TmlpGameData::firstOrCreate(array(
            'center_id'      => $this->statsReport->center->id,
            'quarter_id'     => $this->statsReport->quarter->id,
            'tmlp_game_id'   => $game->id,
            'reporting_date' => $this->statsReport->reportingDate->toDateString(),
        ));
        $gameData->offset = $row;
        $gameData->quarterStartRegistered = $this->reader->getQuarterStartRegistered($row);
        $gameData->quarterStartApproved   = $this->reader->getQuarterStartApproved($row);
        $gameData->statsReportId = $this->statsReport->id;
        $gameData->save();
    }
}
