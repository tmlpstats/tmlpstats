<?php

use TmlpStats\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RoleTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "roles.csv";

    protected function createObject($data)
    {
        Role::create($data);
    }
}
