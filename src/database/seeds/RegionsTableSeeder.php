<?php

class RegionsTableSeeder extends ArraySeeder
{
    protected $table = 'regions';

    protected function initData()
    {
        $this->columns = ['abbreviation', 'name', 'email', 'parent_id'];
        $this->insertData = [
            ['NA', 'North America', 'na.statistician@tmlpstats.com', NULL],
            ['West', 'North America - West', 'west.statistician@tmlpstats.com', 1],
            ['East', 'North America - East', 'east.statistician@tmlpstats.com', 1],
            ['EME', 'Europe & Middle East', 'eme.statistician@tmlpstats.com', NULL],
            ['ANZ', 'Australia & New Zealand', 'anz.statistician@tmlpstats.com', NULL],
            ['IND', 'India', 'india.statistician@tmlpstats.com', NULL],
        ];
    }
}
