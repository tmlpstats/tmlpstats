<?php

use TmlpStats\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "users.csv";

    protected function createObject($data)
    {
        User::create($data);
    }
}
