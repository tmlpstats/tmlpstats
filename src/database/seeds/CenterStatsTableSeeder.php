<?php

use TmlpStats\CenterStats;
use TmlpStats\Seeders\CsvSeederAbstract;

class CenterStatsTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "center_stats.csv";

    protected function createObject($data)
    {
        CenterStats::create($data);
    }
}

