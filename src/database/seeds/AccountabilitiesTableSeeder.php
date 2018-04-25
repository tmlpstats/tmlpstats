<?php

class AccountabilitiesTableSeeder extends ArraySeeder
{
    protected $table = 'accountabilities';

    protected function initData()
    {
        $this->columns = ['name', 'context', 'display'];
        $this->insertData = [
            ['regionalStatistician', 'program', 'Regional Statistician'],
            ['globalStatistician', 'program', 'Global Statistician'],
            ['globalLeader', 'program', 'Global Leader'],
            ['statistician', 'team', 'Statistician'],
            ['statisticianApprentice', 'team', 'Statistician In Training'],
            ['t1tl', 'team', 'Team 1 Team Leader'],
            ['t2tl', 'team', 'Team 2 Team Leader'],
            ['programManager', 'program', 'Program Manager'],
            ['classroomLeader', 'program', 'Classroom Leader'],
            ['cap', 'team', 'Access to Power'],
            ['cpc', 'team', 'Power to Create'],
            ['t1x', 'team', 'T1 Expansion'],
            ['t2x', 'team', 'T2 Expansion'],
            ['gitw', 'team', 'Game in the World'],
            ['lf', 'team', 'Landmark Forum'],
            ['logistics', 'team', 'Logistics'],
        ];
    }
}
