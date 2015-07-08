<?php

use TmlpStats\TmlpRegistrationData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TmlpRegistrationDataTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "tmlp_registrations_data.csv";

    protected function createObject($data)
    {
        TmlpRegistrationData::create($data);
    }
}

