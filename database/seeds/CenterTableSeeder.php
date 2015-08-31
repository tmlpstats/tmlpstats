<?php

use TmlpStats\Center;
use TmlpStats\Seeders\CsvSeederAbstract;

class CenterTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "centers.csv";

    protected function createObject($data)
    {
        Center::create($data);
    }
}
