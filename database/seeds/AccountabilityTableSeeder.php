<?php

use TmlpStats\Accountability;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AccountabilityTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "accountabilities.csv";

    protected function createObject($data)
    {
        Accountability::create($data);
    }
}