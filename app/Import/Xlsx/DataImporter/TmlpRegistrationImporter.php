<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\TmlpRegistration;
use TmlpStats\TmlpRegistrationData;
use TmlpStats\TeamMember;
use TmlpStats\Util;

class TmlpRegistrationImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected static $blockT1Reg = array();
    protected static $blockT2Reg = array();
    protected static $blockFutureReg = array();

    protected function populateSheetRanges()
    {
        $t1Reg = $this->findRange(32, 'Team 1 Registrations', 'Team 2 Registrations');
        self::$blockT1Reg[] = $this->excelRange('A','AG');
        self::$blockT1Reg[] = $this->excelRange($t1Reg['start'] + 1, $t1Reg['end']);

        $t2Reg = $this->findRange($t1Reg['end'], 'Team 2 Registrations', 'Future Weekend Reg');
        self::$blockT2Reg[] = $this->excelRange('A','AG');
        self::$blockT2Reg[] = $this->excelRange($t2Reg['start'] + 1, $t2Reg['end']);

        $futureReg = $this->findRange($t2Reg['end'], 'Future Weekend Reg', 'REMEMBER TO ENTER THE COURSE INFORMATION ON THE "CAP & CPC Course Info" Tab');
        self::$blockFutureReg[] = $this->excelRange('A','AE');
        self::$blockFutureReg[] = $this->excelRange($futureReg['start'] + 1, $futureReg['end']);    }

    public function load()
    {
        $this->reader = $this->getReader($this->sheet);
        $this->reader->setReportingDate($this->statsReport->reportingDate);

        $this->loadBlock(self::$blockT1Reg, 1);
        $this->loadBlock(self::$blockT2Reg, 2);
        $this->loadBlock(self::$blockFutureReg, 'future');
    }

    protected function loadEntry($row, $type)
    {
        if ($this->reader->isEmptyCell($row,'A') && $this->reader->isEmptyCell($row,'B')) return;
        if (defined('IMPORT_HACKS') && strlen($this->reader->getValue($row,'A')) == 1) return; // someone fat fingered a number in the first column in Seattle's stats (Fall 2014)

        $incoming = TmlpRegistration::firstOrCreate(array(
            'first_name' => $this->reader->getFirstName($row),
            'last_name'  => $this->reader->getLastInitial($row),
            'center_id'  => $this->statsReport->center->id,
            'reg_date'   => $this->reader->getRegDate($row),
        ));
        if ($incoming->statsReportId === null) {
            $incoming->statsReportId = $this->statsReport->id;
        }

        $incoming->incomingTeamYear = is_numeric($type)
            ? $type
            : $this->reader->getIncomingTeamYear($row);

        if ($incoming->incomingTeamYear == 'R') {
            $incoming->incomingTeamYear = 2;
        }

        $incoming->save();

        $incomingData = TmlpRegistrationData::firstOrCreate(array(
            'reporting_date'       => $this->statsReport->reportingDate->toDateString(),
            'center_id'            => $this->statsReport->center->id,
            'quarter_id'           => $this->statsReport->quarter->id,
            'tmlp_registration_id' => $incoming->id,
        ));

        $incomingData->offset = $row;
        $incomingData->bef                     = $this->reader->getBef($row);
        $incomingData->dur                     = $this->reader->getDur($row);
        $incomingData->aft                     = $this->reader->getAft($row);
        $incomingData->weekendReg              = $this->reader->getWeekendReg($row);
        $incomingData->appOut                  = $this->reader->getAppOut($row);
        $incomingData->appOutDate              = $this->reader->getAppOutDate($row);
        $incomingData->appIn                   = $this->reader->getAppIn($row);
        $incomingData->appInDate               = $this->reader->getAppInDate($row);
        $incomingData->appr                    = $this->reader->getAppr($row);
        $incomingData->apprDate                = $this->reader->getApprDate($row);
        $incomingData->wd                      = $this->reader->getWd($row);
        $incomingData->wdDate                  = $this->reader->getWdDate($row);
        $incomingData->committedTeamMemberName = $this->reader->getCommittedTeamMemberName($row);
        $incomingData->comment                 = $this->reader->getComment($row);
        $incomingData->incomingWeekend         = is_numeric($type) ? 'current' : $type;
        if ($incomingData->incomingWeekend == 'current') {
            $incomingData->travel              = $this->reader->getTravel($row);
            $incomingData->room                = $this->reader->getRoom($row);
        }
        $incomingData->statsReportId = $this->statsReport->id;
        $incomingData->save();
    }

    public function postProcess()
    {
        $incomingList = TmlpRegistrationData::where('stats_report_id', '=', $this->statsReport->id)->get();
        foreach($incomingList as $incomingData) {

            $teamMember = $this->getTeamMember($incomingData->committedTeamMemberName);

            if ($teamMember) {
                $incomingData->committedTeamMemberId = $teamMember->id;
                $incomingData->save();
            }

            if ($incomingData->bef == 'R' || $incomingData->dur == 'R' || $incomingData->aft == 'R') {
                $incoming = TmlpRegistration::find($incomingData->tmlpRegistrationId);
                if ($incoming && $incoming->incomingTeamYear == 2) {
                    $incoming->isReviewer = true;
                }
            }
        }
    }

    protected function getTeamMember($name)
    {
        $nameParts = Util::getNameParts($name);

        return TeamMember::where('center_id', '=', $this->statsReport->center->id)
                         ->where('first_name', '=', $nameParts['firstName'])
                         ->where('last_name', '=', $nameParts['lastName'])
                         ->first();
    }
}
