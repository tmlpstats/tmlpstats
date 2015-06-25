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

        $this->data[] = array(
            'offset'                  => $row,
            'firstName'               => $this->reader->getFirstName($row),
            'lastName'                => $this->reader->getLastInitial($row),
            'regDate'                 => $this->reader->getRegDate($row),
            'bef'                     => $this->reader->getBef($row),
            'dur'                     => $this->reader->getDur($row),
            'aft'                     => $this->reader->getAft($row),
            'weekendReg'              => $this->reader->getWeekendReg($row),
            'appOut'                  => $this->reader->getAppOut($row),
            'appOutDate'              => $this->reader->getAppOutDate($row),
            'appIn'                   => $this->reader->getAppIn($row),
            'appInDate'               => $this->reader->getAppInDate($row),
            'appr'                    => $this->reader->getAppr($row),
            'apprDate'                => $this->reader->getApprDate($row),
            'wd'                      => $this->reader->getWd($row),
            'wdDate'                  => $this->reader->getWdDate($row),
            'committedTeamMemberName' => $this->reader->getCommittedTeamMemberName($row),
            'comment'                 => $this->reader->getComment($row),
            'incomingWeekend'         => is_numeric($type) ? 'current' : $type,
            'incomingTeamYear'        => is_numeric($type) ? $type : $this->reader->getIncomingTeamYear($row),
            'travel'                  => $this->reader->getTravel($row),
            'room'                    => $this->reader->getRoom($row),
        );
    }

    public function postProcess()
    {
        foreach ($this->data as $incomingInput) {

            $incoming = TmlpRegistration::firstOrNew(array(
                'first_name' => $incomingInput['firstName'],
                'last_name'  => $incomingInput['lastName'],
                'center_id'  => $this->statsReport->center->id,
                'reg_date'   => $incomingInput['regDate'],
            ));
            if ($incoming->statsReportId === null) {
                $incoming->statsReportId = $this->statsReport->id;
            }
            if ($incoming->incomingTeamYear === null) {

                if ($incomingInput['incomingTeamYear'] == 'R') {
                    $incoming->incomingTeamYear = 2;
                    $incoming->isReviewer = true;
                } else {
                    $incoming->incomingTeamYear = $incomingInput['incomingTeamYear'];
                }
            }
            $incoming->save();

            $incomingData = TmlpRegistrationData::firstOrNew(array(
                'reporting_date'       => $this->statsReport->reportingDate->toDateString(),
                'center_id'            => $this->statsReport->center->id,
                'quarter_id'           => $this->statsReport->quarter->id,
                'tmlp_registration_id' => $incoming->id,
            ));

            unset($incomingInput['firstName']);
            unset($incomingInput['lastName']);
            unset($incomingInput['regDate']);
            unset($incomingInput['incomingTeamYear']);

            $incomingData = $this->setValues($incomingData, $incomingInput);

            $teamMember = $this->getTeamMember($incomingData->committedTeamMemberName);
            if ($teamMember) {
                $incomingData->committedTeamMemberId = $teamMember->id;
            }

            $incomingData->statsReportId = $this->statsReport->id;
            $incomingData->save();
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
