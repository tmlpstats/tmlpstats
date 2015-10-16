<?php

use TmlpStats\CenterStatsData;
use TmlpStats\Seeders\CsvSeederAbstract;

class CenterStatsDataTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "center_stats_data.csv";

    protected function createObject($data)
    {
        CenterStatsData::create($data);
    }
}

