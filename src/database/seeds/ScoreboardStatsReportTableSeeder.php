<?php

class ScoreboardStatsReportTableSeeder extends ArraySeeder
{
    protected $table = 'stats_reports';

    protected function initData()
    {
        $this->columns = [
            'id',
            'reporting_date',
            'version',
            'validated',
            'reporting_statistician_id',
            'center_id',
            'quarter_id',
            'user_id',
            'locked',
            'submitted_at',
        ];

        $this->insertData = [
            [10417, '2017-06-09', 'api', 1, NULL, 1, 36, 13, 1, '2017-06-10 02:04:04'],
            [10566, '2017-06-16', 'api', 1, NULL, 1, 36, 13, 1, '2017-06-17 18:12:01'],
            [10635, '2017-06-23', 'api', 1, NULL, 1, 36, 13, 1, '2017-06-24 01:59:34'],
            [10851, '2017-06-30', 'api', 1, NULL, 1, 36, 13, 1, '2017-07-01 01:54:23'],
            [10976, '2017-07-07', 'api', 1, NULL, 1, 36, 13, 1, '2017-07-08 06:36:19'],
            [11020, '2017-07-14', 'api', 1, NULL, 1, 36, 13, 1, '2017-07-15 00:39:20'],
            [11257, '2017-07-21', 'api', 1, NULL, 1, 36, 13, 1, '2017-07-22 01:26:42'],
            [11312, '2017-07-28', 'api', 1, NULL, 1, 36, 13, 1, '2017-07-29 01:21:39'],
            [11537, '2017-08-04', 'api', 1, NULL, 1, 36, 13, 1, '2017-08-05 16:14:40'],
            [11658, '2017-08-11', 'api', 1, NULL, 1, 36, 13, 1, '2017-08-16 02:18:57'],
            [11776, '2017-08-18', 'api', 1, NULL, 1, 36, 13, 1, '2017-08-18 22:45:58'],
        ];
    }
}
