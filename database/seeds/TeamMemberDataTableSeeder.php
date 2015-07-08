<?php

use TmlpStats\TeamMemberData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TeamMemberDataTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "team_members_data.csv";

    protected function createObject($data)
    {
        TeamMemberData::create($data);
    }
}

