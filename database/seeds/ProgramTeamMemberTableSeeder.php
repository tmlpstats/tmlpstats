<?php

use TmlpStats\ProgramTeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ProgramTeamMemberTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "program_team_members.csv";

    protected function createObject($data)
    {
        ProgramTeamMember::create($data);
    }
}

