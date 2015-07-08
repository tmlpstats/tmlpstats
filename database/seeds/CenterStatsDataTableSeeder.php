<?php

use TmlpStats\CenterStatsData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CenterStatsDataTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "center_stats_data.csv";

    protected function createObject($data)
    {
        CenterStatsData::create($data);
    }
}

