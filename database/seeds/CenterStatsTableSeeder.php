<?php

use TmlpStats\CenterStats;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CenterStatsTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "center_stats.csv";

    protected function createObject($data)
    {
        CenterStats::create($data);
    }
}

