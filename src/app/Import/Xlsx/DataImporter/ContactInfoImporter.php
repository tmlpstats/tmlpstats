<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\Accountability;
use TmlpStats\Import\Xlsx\ImportDocument\ImportDocument;
use TmlpStats\Person;
use TmlpStats\ProgramTeamMember;
use TmlpStats\TeamMember;
use TmlpStats\Util;

use Log;

class ContactInfoImporter extends DataImporterAbstract
{
    protected $sheetId = ImportDocument::TAB_LOCAL_TEAM_CONTACT;

    protected $reportingStatistician = null;
    protected $programManagerAttendingWeekend = null;
    protected $classroomLeaderAttendingWeekend = null;

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

        $this->loadEntry(5); // programManager
        $this->loadEntry(6); // classroomLeader
        $this->loadEntry(7); // t2tl
        $this->loadEntry(8); // t1tl
        $this->loadEntry(9); // statistician
        $this->loadEntry(10); // apprentice
//        $this->loadReportingStatistician(); // reportingStatistician
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

//    protected function loadReportingStatistician()
//    {
//        $this->data[] = array(
//            'offset'         => $this->reader->getReportingStatisticianNameRow(),
//            'accountability' => 'Reporting Statistician',
//            'name'           => $this->reader->getReportingStatisticianName(),
//            'phone'          => $this->reader->getReportingStatisticianPhone(),
//            'email'          => $this->statsReport->center->statsEmail,
//        );
//    }

    protected function loadProgramLeadersAttendingWeekend()
    {
        $this->programManagerAttendingWeekend = $this->reader->getProgramManagerAttendingWeekend();
        $this->classroomLeaderAttendingWeekend = $this->reader->getClassroomLeaderAttendingWeekend();
    }

    /**
     *
     */
    public function postProcess()
    {
        foreach ($this->data as $leader) {

            $accountability = Accountability::name($this->mapAccountabilities($leader['accountability']))->first();

            if ($leader['name'] === null || strtoupper($leader['name']) == 'NA' || strtoupper($leader['name']) == 'N/A') {

                $currentAccountable = $this->statsReport->center->getAccountable($accountability);
                if ($currentAccountable) {
                    $currentAccountable->removeAccountability($accountability);
                }
                continue;
            }

            $nameParts = Util::getNameParts($leader['name']);

            $member = null;

            $possibleMembers = Person::firstName($nameParts['firstName'])
                ->lastName($nameParts['lastName'])
                ->byCenter($this->statsReport->center)
                ->get();

            if ($possibleMembers->count() == 1) {
                $member = $possibleMembers->first();
            } else if ($possibleMembers->count() > 1) {
                $sameEmailMembers = $possibleMembers->where('email', '=', $leader['email']);

                if ($sameEmailMembers->count() == 1) {
                    $member = $sameEmailMembers->first();
                } else {
                    $teamMembers = $possibleMembers->where('identifier', 'LIKE', 'q:%');
                    if ($teamMembers->count() == 1) {
                        $member = $teamMembers->first();
                    }
                }
            }

            if (!$member) {
                $member = Person::create([
                    'center_id'     => $this->statsReport->center->id,
                    'first_name'    => $nameParts['firstName'],
                    'last_name'     => $nameParts['lastName'],
                ]);
            }

            if ($accountability && !$member->hasAccountability($accountability)) {

                $currentAccountable = $this->statsReport->center->getAccountable($accountability);
                if ($currentAccountable) {
                    $currentAccountable->removeAccountability($accountability);
                }
                $member->addAccountability($accountability);
            } else if (!$accountability) {
                Log::error("Unable to find accountability '{$leader['accountability']}' for center {$this->statsReport->center->id}");
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

    protected function mapAccountabilities($displayString)
    {

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

    protected function populateSheetRanges()
    {
    } // no blocks to load in this sheet
}
