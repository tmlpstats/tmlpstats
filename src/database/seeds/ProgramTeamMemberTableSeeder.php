<?php

use TmlpStats\ProgramTeamMember;
use TmlpStats\Seeders\CsvSeederAbstract;

class ProgramTeamMemberTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "program_team_members.csv";

    protected function createObject($data)
    {
        ProgramTeamMember::create($data);
    }
}

