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

            if (isset($this->data['errors'])) {
                continue;
            }

            $accountability = Accountability::name($this->mapAccountabilities($leader['accountability']))->first();

            if ($leader['name'] === null || strtoupper($leader['name']) == 'NA' || strtoupper($leader['name']) == 'N/A') {

                $currentAccountable = $this->statsReport->center->getAccountable($accountability);
                if ($currentAccountable) {
                    $currentAccountable->removeAccountability($accountability);
                }
                continue;
            }

            $member = $this->getPerson($leader);

            if ($accountability && !$member->hasAccountability($accountability)) {

                $currentAccountable = $this->statsReport->center->getAccountable($accountability->name);
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

    protected function getPerson($leader)
    {
        $nameParts = Util::getNameParts($leader['name']);

        $member = null;

        $possibleMembers = Person::firstName($nameParts['firstName'])
            ->lastName($nameParts['lastName'])
            ->byCenter($this->statsReport->center)
            ->get();

        if ($possibleMembers->count() == 1) {
            $member = $possibleMembers->first();
        } else if ($possibleMembers->count() > 1) {

            // There are multiple people with that name at this center. Try searching by email
            $sameEmailMembers = $possibleMembers->where('email', '=', $leader['email'])->get();

            if ($sameEmailMembers->count() > 0) {
                // If we found any, it's for sure the same person
                $member = $sameEmailMembers->first();
            } else {
                // If not, try searching for just team members
                $teamMembers = $possibleMembers->where('identifier', 'LIKE', 'q:%')->get();
                if ($teamMembers->count() > 0) {
                    // If we found any, good enough
                    $member = $teamMembers->first();
                } else {
                    // fine, just give me the first one
                    $member = $possibleMembers->first();
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

        return $member;
    }

    protected function mapAccountabilities($displayString)
    {
        switch ($displayString) {
            case 'Program Manager':
                return 'programManager';
            case 'Classroom Leader':
                return 'classroomLeader';
            case 'Team 1 Team Leader':
                return 't1tl';
            case 'Team 2 Team Leader':
                return 't2tl';
            case 'Statistician':
                return 'statistician';
            case 'Statistician Apprentice':
                return 'statisticianApprentice';
            default:
                return null;
        }
    }

    protected function populateSheetRanges()
    {
        // no blocks to load in this sheet
    }
}
