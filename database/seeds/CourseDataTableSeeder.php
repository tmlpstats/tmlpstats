<?php

use TmlpStats\CourseData;
use TmlpStats\Seeders\CsvSeederAbstract;

class CourseDataTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "courses_data.csv";

    protected function createObject($data)
    {
        CourseData::create($data);
    }
}

