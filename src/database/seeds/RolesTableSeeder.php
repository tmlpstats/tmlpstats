<?php

class RolesTableSeeder extends ArraySeeder
{
    protected $table = 'roles';

    protected function initData()
    {
        $this->columns = ['name', 'display'];
        $this->insertData = [
            ['administrator', 'Administrator'],
            ['globalStatistician', 'Global Statistician'],
            ['localStatistician', 'Local Statistician'],
            ['readonly', 'Read-Only'],
            ['programLeader', 'Program Leader'],
        ];
    }
}
