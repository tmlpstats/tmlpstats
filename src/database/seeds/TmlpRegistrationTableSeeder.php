<?php

use TmlpStats\TmlpRegistration;
use TmlpStats\Seeders\CsvSeederAbstract;

class TmlpRegistrationTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "tmlp_registrations.csv";

    protected function createObject($data)
    {
        TmlpRegistration::create($data);
    }
}

