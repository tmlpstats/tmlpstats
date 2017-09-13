<?php

class ScoreboardGlobalReportStatsReportTableSeeder extends ArraySeeder
{
    protected $table = 'global_report_stats_report';

    protected function initData()
    {
        $this->columns = [
            'stats_report_id',
            'global_report_id',
        ];

        $this->insertData = [
            [10417, 99],
            [10566, 84],
            [10635, 100],
            [10851, 101],
            [10976, 102],
            [11020, 103],
            [11257, 104],
            [11312, 105],
            [11537, 106],
            [11658, 107],
            [11776, 108],
        ];

    }
}
