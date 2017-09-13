<?php

class QuartersTableSeeder extends ArraySeeder
{
    protected $table = 'quarters';

    protected function initData()
    {
        $this->columns = ['id', 't1_distinction', 't2_distinction', 'quarter_number', 'year'];
        $this->insertData = [
            [1, 'Completion', 'Completion', 2, 2014],
            [2, 'Relatedness', 'Generosity', 3, 2014],
            [3, 'Possibility', 'Integrity', 4, 2014],
            [4, 'Opportunity', 'Listening', 1, 2015],
            [5, 'Action', 'Responsibility', 2, 2015],
            [6, 'Completion', 'Completion', 3, 2015],
            [7, 'Relatedness', 'Generosity', 4, 2015],
            [8, 'Possibility', 'Integrity', 1, 2016],
            [24, 'Opportunity', 'Listening', 2, 2016],
            [28, 'Completion', 'Completion', 1, 2013],
            [29, 'Relatedness', 'Generosity', 2, 2013],
            [30, 'Possibility', 'Integrity', 3, 2013],
            [31, 'Opportunity', 'Listening', 4, 2013],
            [32, 'Action', 'Responsibility', 1, 2014],
            [33, 'Action', 'Responsibility', 3, 2016],
            [34, 'Completion', 'Completion', 4, 2016],
            [35, 'Relatedness', 'Generosity', 1, 2017],
            [36, 'Possibility', 'Integrity', 2, 2017],
            [37, 'Opportunity', 'Listening', 3, 2017],
            [38, 'Action', 'Responsibility', 4, 2017],
            [39, 'Completion', 'Completion', 1, 2018],
            [40, 'Relatedness', 'Generosity', 2, 2018],
            [41, 'Possibility', 'Integrity', 3, 2018],
            [42, 'Opportunity', 'Listening', 4, 2018],
            [43, 'Action', 'Responsibility', 1, 2019],
            [44, 'Completion', 'Completion', 2, 2019],
        ];
    }
}
