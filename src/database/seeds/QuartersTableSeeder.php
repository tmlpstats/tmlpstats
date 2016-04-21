<?php

class QuartersTableSeeder extends ArraySeeder
{
    protected $table = 'quarters';

    protected function initData()
    {
        $this->columns = ['t1_distinction', 't2_distinction', 'quarter_number', 'year'];
        $this->insertData = [
            ['Relatedness', 'Generosity', 4, 2015],
            ['Possibility', 'Integrity', 1, 2016],
            ['Opportunity', 'Listening', 2, 2016],
            ['Action', 'Responsibility', 3, 2016],
            ['Completion', 'Completion', 4, 2016],
        ];
    }
}
