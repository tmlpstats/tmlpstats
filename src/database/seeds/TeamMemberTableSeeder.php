<?php

use TmlpStats\TeamMember;
use TmlpStats\Seeders\CsvSeederAbstract;

class TeamMemberTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "team_members.csv";

    protected function createObject($data)
    {
        TeamMember::create($data);
    }
}

