<?php

class RegionQuarterDetailsTableSeeder extends ArraySeeder
{
    protected $table = 'region_quarter_details';

    protected function initData()
    {
        $this->columns = [
            'quarter_id',
            'region_id',
            'location',
            'start_weekend_date',
            'end_weekend_date',
            'classroom1_date',
            'classroom2_date',
            'classroom3_date',
        ];
        $this->insertData = [
            [1, 1, 'Chicago', '2015-11-20', '2016-02-19', '2015-12-04', '2016-01-08', '2016-02-05'],
            [2, 1, 'Seattle', '2016-02-19', '2016-06-10', '2016-03-18', '2016-04-15', '2016-05-13'],
            [3, 1, 'Los Angeles', '2016-06-10', '2016-08-19', '2016-06-24', '2016-07-15', '2015-08-05'],
            [4, 1, 'Atlanta', '2016-08-19', '2016-11-18', '0000-00-00', '0000-00-00', '0000-00-00'],
            [5, 1, 'Dallas', '2016-11-18', '2017-02-17', '0000-00-00', '0000-00-00', '0000-00-00'],
        ];
    }
}
