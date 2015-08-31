<?php

use TmlpStats\TmlpRegistrationData;
use TmlpStats\Seeders\CsvSeederAbstract;

class TmlpRegistrationDataTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "tmlp_registrations_data.csv";

    protected function createObject($data)
    {
        TmlpRegistrationData::create($data);
    }
}

