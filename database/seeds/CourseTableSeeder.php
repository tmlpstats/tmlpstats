<?php

use TmlpStats\Course;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CourseTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "courses.csv";

    protected function createObject($data)
    {
        Course::create($data);
    }
}

