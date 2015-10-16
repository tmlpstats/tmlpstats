<?php

use TmlpStats\TeamMemberData;
use TmlpStats\Seeders\CsvSeederAbstract;

class TeamMemberDataTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "team_members_data.csv";

    protected function createObject($data)
    {
        TeamMemberData::create($data);
    }
}

