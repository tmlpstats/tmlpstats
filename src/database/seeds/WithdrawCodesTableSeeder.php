<?php

class WithdrawCodesTableSeeder extends ArraySeeder
{
    protected $table = 'withdraw_codes';

    protected function initData()
    {
        $this->columns = ['code', 'display'];
        $this->insertData = [
            ['AP', "Chose another program"],
            ['NW', "Doesn't want the training"],
            ['FIN', "Financial"],
            ['FW', "Moved to a future weekend"],
            ['MOA', "Moved out of area"],
            ['NA', "Not approved"],
            ['OOC', "Out of communication"],
            ['T', "Time conversation"],
            ['RE', "Registration error"],
            ['WB', "Well-being out"],
        ];
    }
}











