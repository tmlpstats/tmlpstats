<?php

use TmlpStats\StatsReport;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class StatsReportTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "stats_reports.csv";

    protected function createObject($data)
    {
        StatsReport::create($data);
    }
}

