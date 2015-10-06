<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Accountability;
use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Person;
use TmlpStats\ProgramTeamMember;
use TmlpStats\TeamMember;
use TmlpStats\Util;

class ContactInfoImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_LOCAL_TEAM_CONTACT;

    protected $reportingStatistician           = NULL;
    protected $programManager                  = NULL;
    protected $classroomLeader                 = NULL;
    protected $t2tl                            = NULL;
    protected $t1tl                            = NULL;
    protected $statistician                    = NULL;
    protected $apprentice                      = NULL;
    protected $programManagerAttendingWeekend  = NULL;
    protected $classroomLeaderAttendingWeekend = NULL;

    public function getReportingStatistician()
    {
        return $this->reportingStatistician;
    }
    public function getProgramManagerAttendingWeekend()
    {
        return $this->programManagerAttendingWeekend;
    }
    public function getClassroomLeaderAttendingWeekend()
    {
        return $this->classroomLeaderAttendingWeekend;
    }

    protected function load()
    {
        $this->reader = $this->getReader($this->sheet);

        $this->programManager        = $this->loadEntry(5);
        $this->classroomLeader       = $this->loadEntry(6);
        $this->t2tl                  = $this->loadEntry(7);
        $this->t1tl                  = $this->loadEntry(8);
        $this->statistician          = $this->loadEntry(9);
        $this->apprentice            = $this->loadEntry(10);
//        $this->reportingStatistician = $this->loadReportingStatistician();
        $this->loadProgramLeadersAttendingWeekend();
    }

    protected function loadEntry($row, $unused = null)
    {
        $this->data[] = array(
            'offset'         => $row,
            'accountability' => $this->reader->getAccountability($row),
            'name'           => $this->reader->getName($row),
            'phone'          => $this->reader->getPhone($row),
            'email'          => $this->reader->getEmail($row),
        );
    }

    protected function loadReportingStatistician()
    {
        $this->data[] = array(
            'offset'         => $this->reader->getReportingStatisticianNameRow(),
            'accountability' => 'Reporting Statistician',
            'name'           => $this->reader->getReportingStatisticianName(),
            'phone'          => $this->reader->getReportingStatisticianPhone(),
            'email'          => $this->statsReport->center->statsEmail,
        );
    }

    protected function loadProgramLeadersAttendingWeekend()
    {
        $this->programManagerAttendingWeekend = $this->reader->getProgramManagerAttendingWeekend();
        $this->classroomLeaderAttendingWeekend = $this->reader->getClassroomLeaderAttendingWeekend();
    }

    public function postProcess()
    {
        foreach ($this->data as $leader) {

            if ($leader['name'] === NULL || strtoupper($leader['name']) == 'NA' || strtoupper($leader['name']) == 'N/A') {
                continue;
            }

            $nameParts = Util::getNameParts($leader['name']);

            $member = Person::firstOrNew(array(
                'center_id'      => $this->statsReport->center->id,
                'first_name'     => $nameParts['firstName'],
                'last_name'      => $nameParts['lastName'],
            ));

            // TODO: Handle error gracefully
            $accountability = Accountability::name($this->mapAccountabilities($leader['accountability']))->first();
            if ($accountability) {
                $member->addAccountability($accountability);
            }

            unset($leader['name']);
            unset($leader['offset']);
            unset($leader['accountability']);

            $this->setValues($member, $leader);

            if (!$member->exists || $member->isDirty()) {
                $member->save();
            }
        }
    }

    protected function mapAccountabilities($displayString) {

        switch ($displayString) {
            case 'Program Manager':
                return 'programManager';
            case 'Classroom Leader':
                return 'classroomLeader';
            case 'Team 1 Team Leader':
                return 'team1TeamLeader';
            case 'Team 2 Team Leader':
                return 'team2TeamLeader';
            case 'Statistician':
                return 'teamStatistician';
            case 'Statistician Apprentice':
                return 'teamStatisticianApprentice';
            default:
                return null;
        }
    }

    protected function populateSheetRanges() { } // no blocks to load in this sheet
}
