<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Person;
use TmlpStats\Quarter;
use TmlpStats\TmlpRegistration;
use TmlpStats\TmlpRegistrationData;
use TmlpStats\TeamMember;
use TmlpStats\Util;
use TmlpStats\WithdrawCode;

class TmlpRegistrationImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_WEEKLY_STATS;

    protected $blockT1Reg = array();
    protected $blockT2Reg = array();
    protected $blockFutureReg = array();

    protected function populateSheetRanges()
    {
        $t1Reg = $this->findRange(32, 'Team 1 Registrations', 'Team 2 Registrations');
        $this->blockT1Reg[] = $this->excelRange('A','AG');
        $this->blockT1Reg[] = $this->excelRange($t1Reg['start'] + 1, $t1Reg['end']);

        $t2Reg = $this->findRange($t1Reg['end'], 'Team 2 Registrations', 'Future Weekend Reg');
        $this->blockT2Reg[] = $this->excelRange('A','AG');
        $this->blockT2Reg[] = $this->excelRange($t2Reg['start'] + 1, $t2Reg['end']);

        $futureReg = $this->findRange($t2Reg['end'], 'Future Weekend Reg', 'REMEMBER TO ENTER THE COURSE INFORMATION ON THE "CAP & CPC Course Info" Tab');
        $this->blockFutureReg[] = $this->excelRange('A','AE');
        $this->blockFutureReg[] = $this->excelRange($futureReg['start'] + 1, $futureReg['end']);
    }

    public function load()
    {
        $this->reader = $this->getReader($this->sheet);
        $this->reader->setReportingDate($this->statsReport->reportingDate);

        $this->loadBlock($this->blockT1Reg, 1);
        $this->loadBlock($this->blockT2Reg, 2);
        $this->loadBlock($this->blockFutureReg, 'future');
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

            if ($incoming->teamYear === null) {

                if ($incomingInput['incomingTeamYear'] == 'R') {
                    $incoming->teamYear = 2;
                    $incoming->isReviewer = true;
                } else {
                    $incoming->teamYear = $incomingInput['incomingTeamYear'];
                }
            }
            $incoming->save();

            $incomingData = TmlpRegistrationData::firstOrNew(array(
                'tmlp_registration_id' => $incoming->id,
                'stats_report_id'      => $this->statsReport->id,
            ));

            $withdrawCodeId = null;
            if ($incomingInput['wd']) {
                $code = substr($incomingInput['wd'], 2);
                $withdrawCode = WithdrawCode::code($code)->first();
                $incomingData->withdrawCodeId = $withdrawCode ? $withdrawCode->id : null;
            }

            $thisQuarter = $this->statsReport->quarter;
            if ($incomingInput['incomingWeekend'] === 'current') {
                $quarterNumber = ($thisQuarter->quarterNumber + 1) % 5;
            } else {
                $quarterNumber = ($thisQuarter->quarterNumber + 2) % 5;
            }

            $quarterNumber = $quarterNumber ?: 1; // no quarter 0

            $year = ($quarterNumber === 1)
                ? $thisQuarter->year + 1
                : $thisQuarter->year;

            $nextQuarter = Quarter::year($year)->quarterNumber($quarterNumber)->first();

            if ($nextQuarter) {
                $incomingData->incomingQuarterId = $nextQuarter->id;
            }

            $incomingInput['travel'] = $incomingInput['travel'] ? true : false;
            $incomingInput['room'] = $incomingInput['room'] ? true : false;

            $teamMember = $this->getTeamMember($incomingInput['committedTeamMemberName']);
            if ($teamMember) {
                $incomingData->committedTeamMemberId = $teamMember->id;
            }

            unset($incomingInput['firstName']);
            unset($incomingInput['lastName']);
            unset($incomingInput['incomingTeamYear']);
            unset($incomingInput['offset']);
            unset($incomingInput['bef']);
            unset($incomingInput['dur']);
            unset($incomingInput['aft']);
            unset($incomingInput['weekendReg']);
            unset($incomingInput['appOut']);
            unset($incomingInput['appIn']);
            unset($incomingInput['appr']);
            unset($incomingInput['wd']);
            unset($incomingInput['committedTeamMemberName']);
            unset($incomingInput['incomingWeekend']);

            $incomingData = $this->setValues($incomingData, $incomingInput);

            $incomingData->save();
        }
    }

    protected function getTeamMember($name)
    {
        if (!$name) {
            return null;
        }
        $nameParts = Util::getNameParts($name);

        $person = Person::firstName($nameParts['firstName'])
            ->lastName($nameParts['lastName'])
            ->byCenter($this->statsReport->center)
            ->first();

        return $person
            ? TeamMember::byPerson($person)->first()
            : null;
    }
}
