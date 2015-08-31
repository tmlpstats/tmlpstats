<?php

use TmlpStats\User;
use TmlpStats\Seeders\CsvSeederAbstract;

class UserTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "users.csv";

    protected function createObject($data)
    {
        User::create($data);
    }
}
