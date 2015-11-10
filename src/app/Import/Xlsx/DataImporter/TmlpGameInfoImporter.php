<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use Carbon\Carbon;
use DB;
use TmlpStats\Center;
use TmlpStats\GlobalReport;
use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Quarter;
use TmlpStats\StatsReport;
use TmlpStats\TmlpGame;
use TmlpStats\TmlpGameData;

class TmlpGameInfoImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_COURSES;

    protected $blockT1X = array();
    protected $blockT2X = array();

    protected function populateSheetRanges()
    {
        $t1x = $this->findRange(28, 'Game', 'Total', 'B', 'A');
        $this->blockT1X[] = $this->excelRange('A', 'K');
        $this->blockT1X[] = $this->excelRange($t1x['start'] + 1, $t1x['end']);

        $t2x = $this->findRange($t1x['end'] + 1, 'Game', 'Total', 'B', 'A');
        $this->blockT2X[] = $this->excelRange('A', 'K');
        $this->blockT2X[] = $this->excelRange($t2x['start'] + 1, $t2x['end']);
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->loadBlock($this->blockT1X, 'T1X');
        $this->loadBlock($this->blockT2X, 'T2X');
    }

    protected function loadEntry($row, $type)
    {
        if ($this->reader->isEmptyCell($row, 'B')) return;

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

            $gameData = $this->getGameData($gameInput['type'], $this->statsReport->center, $this->statsReport->quarter);

            if ($gameData) {
                // Only import once
                continue;
            }

            $gameData = TmlpGameData::firstOrNew(array(
                'type'            => $gameInput['type'],
                'stats_report_id' => $this->statsReport->id,
            ));

            unset($gameInput['type']);
            unset($gameInput['offset']);

            $this->setValues($gameData, $gameInput);

            $gameData->save();
        }
    }

    public function getGameData($type, Center $center, Quarter $quarter)
    {
        $firstWeek = clone $quarter->startWeekendDate;
        $firstWeek->addWeek();

        $globalReport = null;
        $statsReport = null;

        // Don't reuse game data on the first week, as they may change between submits
        if ($this->statsReport->reportingDate->ne($firstWeek)) {
            $globalReport = GlobalReport::reportingDate($firstWeek)->first();
        }

        if ($globalReport) {
            $statsReport = $globalReport->statsReports()->byCenter($center)->first();
        }

        // If not, search from the beginning until we find it
        if (!$statsReport) {
            $statsReport = $this->findFirstWeek($center, $quarter, $type);
        }

        if (!$statsReport || $statsReport->reportingDate->eq($this->statsReport->reportingDate)) {
            return null;
        }

        return TmlpGameData::type($type)
            ->byStatsReport($statsReport)
            ->first();
    }

    protected $gamesStatsReport = null;
    public function findFirstWeek(Center $center, Quarter $quarter, $type)
    {
        // Promises should all be saved during the same week. Let's remember where we found the
        // last one.
        if ($this->gamesStatsReport) {
            return $this->gamesStatsReport;
        }

        $statsReportResult = DB::table('stats_reports')
            ->select('stats_reports.id')
            ->join('tmlp_games_data', 'tmlp_games_data.stats_report_id', '=', 'stats_reports.id')
            ->join('global_report_stats_report', 'global_report_stats_report.stats_report_id', '=', 'stats_reports.id')
            ->join('global_reports', 'global_reports.id', '=', 'global_report_stats_report.global_report_id')
            ->where('stats_reports.center_id', '=', $center->id)
            ->where('global_reports.reporting_date', '>', $quarter->startWeekendDate)
            ->where('tmlp_games_data.type', '=', $type)
            ->orderBy('global_reports.reporting_date', 'ASC')
            ->first();

        if ($statsReportResult) {
            $this->gamesStatsReport = StatsReport::find($statsReportResult->id);
        }

        return $this->gamesStatsReport;
    }
}
