<?php

class ScoreboardGlobalReportTableSeeder extends ArraySeeder
{
    protected $table = 'global_reports';

    protected function initData()
    {
        $this->columns = [
            'id',
            'reporting_date',
        ];

        $this->insertData = [
            [99, '2017-06-09'],
            [84, '2017-06-16'],
            [100, '2017-06-23'],
            [101, '2017-06-30'],
            [102, '2017-07-07'],
            [103, '2017-07-14'],
            [104, '2017-07-21'],
            [105, '2017-07-28'],
            [106, '2017-08-04'],
            [107, '2017-08-11'],
            [108, '2017-08-18'],
        ];

    }
}
