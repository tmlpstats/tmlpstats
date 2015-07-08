<?php

use TmlpStats\CourseData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CourseDataTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "courses_data.csv";

    protected function createObject($data)
    {
        CourseData::create($data);
    }
}

