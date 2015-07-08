<?php

use TmlpStats\Center;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CenterTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "centers.csv";

    protected function createObject($data)
    {
        Center::create($data);
    }
}
