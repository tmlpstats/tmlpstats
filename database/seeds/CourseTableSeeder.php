<?php

use TmlpStats\Course;
use TmlpStats\Seeders\CsvSeederAbstract;

class CourseTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "courses.csv";

    protected function createObject($data)
    {
        Course::create($data);
    }
}

