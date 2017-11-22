<?php

class WithdrawCodesTableSeeder extends ArraySeeder
{
    protected $table = 'withdraw_codes';

    protected function initData()
    {
        $this->columns = [
            'id',
            'code',
            'display',
            'description',
            'active',
            'context',
            'created_at',
            'updated_at',
        ];

        $this->insertData = [
            [1, 'AP', 'Chose another program', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [2, 'NW', 'Doesn\'t want the training', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [3, 'FIN', 'Financial', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [4, 'FW', 'Moved to a future weekend', NULL, 0, 'application', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [5, 'MOA', 'Moved out of area', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [6, 'NA', 'Not approved', NULL, 1, 'application', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [7, 'OOC', 'Out of communication', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [8, 'T', 'Time conversation', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [9, 'RE', 'Registration error', NULL, 1, 'application', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
            [10, 'WB', 'Well-being out', NULL, 1, 'all', '2015-10-14 03:56:40', '2015-10-14 03:56:40'],
        ];
    }
}


