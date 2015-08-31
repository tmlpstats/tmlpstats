<?php

use TmlpStats\Quarter;
use TmlpStats\Seeders\CsvSeederAbstract;

class QuarterTableSeeder extends CsvSeederAbstract {

    protected $exportFile = 'quarters.csv';

    protected function createObject($data)
    {
        Quarter::create($data);
    }
}
