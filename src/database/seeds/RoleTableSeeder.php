<?php

use TmlpStats\Role;
use TmlpStats\Seeders\CsvSeederAbstract;

class RoleTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "roles.csv";

    protected function createObject($data)
    {
        Role::create($data);
    }
}
