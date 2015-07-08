<?php

use TmlpStats\Quarter;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class QuarterTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = 'quarters.csv';

    protected function createObject($data)
    {
        Quarter::create($data);
    }
}
