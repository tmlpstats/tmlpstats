<?php

use TmlpStats\Seeders\CsvSeederAbstract;
use TmlpStats\StatsReport;

class StatsReportTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "stats_reports.csv";

    protected function createObject($data)
    {
        StatsReport::create($data);
    }
}

