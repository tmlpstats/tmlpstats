<?php

use TmlpStats\TmlpRegistration;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TmlpRegistrationTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "tmlp_registrations.csv";

    protected function createObject($data)
    {
        TmlpRegistration::create($data);
    }
}

