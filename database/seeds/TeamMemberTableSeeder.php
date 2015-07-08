<?php

use TmlpStats\TeamMember;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TeamMemberTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "team_members.csv";

    protected function createObject($data)
    {
        TeamMember::create($data);
    }
}

