<?php
namespace TmlpStats\Import\Xlsx\DataImporter;

use TmlpStats\ProgramTeamMember;
use TmlpStats\TeamMember;
use TmlpStats\Util;

class ContactInfoImporter extends DataImporterAbstract
{
    protected $classDisplayName = "Local Team Contact Info";

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
        // $this->reportingStatistician = $this->loadReportingStatistician();
        $this->loadProgramLeadersAttendingWeekend();
    }

    protected function loadEntry($row, $unused = null)
    {
        $name           = $this->reader->getName($row);
        $accountability = $this->reader->getAccountability($row);

        if ($name === NULL || strtoupper($name) == 'NA' || strtoupper($name) == 'N/A') {
            $this->addMessage("No name provided for $accountability", 'warn', $row);
            return NULL; // It's possible that a center may not have a program manager
        }

        if (defined('IMPORT_HACKS') && strpos($name, '/') !== false) {
            $name = str_replace('/', ' ', $name);
        } else if (strpos($name, '/') !== false) {
            $this->addMessage("Please provide name like 'Jane D' with a space, not a '/' separating the first name and last initial.", 'error', $row);
        }
        $nameParts = Util::getNameParts($name);

        if (strtoupper($nameParts['firstName']) == $nameParts['firstName']) {
            $this->addMessage("First name is in all capital letters. Please provide them with appropriate capitalization, otherwise we may not be able to find this person in some cases.", 'warn', $this->reader->getReportingStatisticianNameRow());
        }

        $member = ProgramTeamMember::firstOrCreate(array(
            'center_id'      => $this->statsReport->center->id,
            'quarter_id'     => $this->statsReport->quarter->id,
            'accountability' => $accountability,
            'first_name'     => $nameParts['firstName'],
            'last_name'      => $nameParts['lastName'],
        ));
        $member->offset = $row;
        $member->phone = $this->reader->getPhone($row);
        $member->email = $this->reader->getEmail($row);

        if ($this->isValid()) {
            if ($member->isDirty()) {
                if ($member->statsReportId === null) {
                    $member->statsReportId = $this->statsReport->id;
                }
                $member->save();
            }
            return $member;
        }
        return NULL;
    }

    protected function loadProgramLeadersAttendingWeekend()
    {
        $this->programManagerAttendingWeekend = $this->reader->getProgramManagerAttendingWeekend();
        $this->classroomLeaderAttendingWeekend = $this->reader->getClassroomLeaderAttendingWeekend();
    }

    // TODO: implement setting the reporting statistician after validation
    public function postProcess()
    {
        $this->reportingStatistician = $this->loadReportingStatistician();

        $programTeamMembers = ProgramTeamMember::where('stats_report_id', '=', $this->statsReport->id)->get();
        foreach ($programTeamMembers as $member) {

            $member->teamMember = $this->getTeamMember;
            $member->save();
        }
    }

    protected function loadReportingStatistician()
    {
        $accountability = 'Reporting Statistician';

        $name = $this->reader->getReportingStatisticianName();

        if (defined('IMPORT_HACKS') && strpos($name, '/') !== false) {
            $name = str_replace('/', ' ', $name);
        } else if (strpos($name, '/') !== false) {
            $this->addMessage("Please provide name like 'Jane D' with a space, not a '/' separating the first name and last initial.", 'error', $row);
        }

        $nameParts = Util::getNameParts($name);

        if (strtoupper($nameParts['firstName']) == $nameParts['firstName']) {
            $this->addMessage("First name is in all capital letters. Please provide them with appropriate capitalization, otherwise we may not be able to find this person in some cases.", 'warn', $this->reader->$this->reader->getReportingStatisticianNameRow());
        }

        $member = ProgramTeamMember::firstOrCreate(array(
            'center_id'  => $this->statsReport->center->id,
            'quarter_id' => $this->statsReport->quarter->id,
            'first_name' => $nameParts['firstName'],
            'last_name'  => $nameParts['lastName'],
        ));

        if (!$member->accountability) {
            $member->offset = $this->reader->getReportingStatisticianNameRow();
            $member->accountability = $accountability;
            $member->email = $member->center->statsEmail;
            $member->statsReportId = $this->statsReport->id;
        }

        $member->phone = $this->reader->getReportingStatisticianPhone();
        if ($this->isValid()) {
            $member->save();
            return $member;
        }
        return NULL;
    }

    protected function getTeamMember($programTeamMember)
    {
        return TeamMember::where('center_id', '=', $this->statsReport->center->id)
                         ->where('first_name', '=', $programTeamMember['firstName'])
                         ->where('last_name', '=', $programTeamMember['lastName'])
                         ->first();
    }

    protected function populateSheetRanges() { } // no blocks to load in this sheet
}
