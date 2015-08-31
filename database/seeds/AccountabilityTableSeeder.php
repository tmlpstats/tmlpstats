<?php

use TmlpStats\Accountability;
use TmlpStats\Seeders\CsvSeederAbstract;

class AccountabilityTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "accountabilities.csv";

    protected function createObject($data)
    {
        Accountability::create($data);
    }
}
