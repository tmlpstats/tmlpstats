<?php

class CentersTableSeeder extends ArraySeeder
{
    protected $table = 'centers';

    protected function initData()
    {
        $this->columns = ['name', 'abbreviation', 'region_id', 'stats_email', 'sheet_filename', 'sheet_version', 'timezone', 'active'];
        $this->insertData = [
            ['Vancouver', 'VAN', 2, 'vancouver@tmlpstats.com', 'Vancouver', '16.1.3', 'America/Vancouver', '1'],
            ['Boston', 'BOS', 3, 'boston@tmlpstats.com', 'Boston', '16.1.3', 'America/New_York', '1'],
            ['Los Angeles', 'LA', 2, 'la@tmlpstats.com', 'LA', '16.1.3', 'America/Los_Angeles', '1'],
            ['MSP', 'MSP', 3, 'msp@tmlpstats.com', 'MSP', '16.1.3', 'America/Chicago', '1'],
            ['Orange County', 'OC', 2, 'orangecounty@tmlpstats.com', 'OC', '16.1.3', 'America/Los_Angeles', '1'],
            ['Phoenix', 'PHO', 2, 'phoenix@tmlpstats.com', 'Phoenix', '16.1.3', 'America/Phoenix', '1'],
            ['San Diego', 'SD', 2, 'sandiego@tmlpstats.com', 'SD', '16.1.3', 'America/Los_Angeles', '1'],
            ['Seattle', 'SEA', 2, 'seattle@tmlpstats.com', 'Seattle', '16.1.3', 'America/Los_Angeles', '1'],
            ['San Francisco', 'SF', 2, 'sanfrancisco@tmlpstats.com', 'SF', '16.1.3', 'America/Los_Angeles', '1'],
            ['San Jose', 'SJ', 2, 'sanjose@tmlpstats.com', 'SJ', '16.1.3', 'America/Los_Angeles', '1'],
            ['Washington, DC', 'WDC', 3, 'washingtondc@tmlpstats.com', 'WDC', '16.1.3', 'America/New_York', '1'],
            ['Mexico', 'MEX', 2, 'mexico@tmlpstats.com', 'Mexico', '16.1.3', 'America/Mexico_City', '1'],
            ['Atlanta', 'ATL', 3, 'atlanta@tmlpstats.com', 'Atlanta', '16.1.3', 'America/New_York', '1'],
            ['Chicago', 'CHI', 3, 'chicago@tmlpstats.com', 'Chicago', '16.1.3', 'America/Chicago', '1'],
            ['Dallas', 'DFW', 2, 'dfw@tmlpstats.com', 'Dallas', '16.1.3', 'America/Chicago', '1'],
            ['Denver', 'DEN', 2, 'den@tmlpstats.com', 'Denver', '16.1.3', 'America/Denver', '1'],
            ['Detroit', 'DET', 3, 'detroit@tmlpstats.com', 'Detroit', '16.1.3', 'America/Detroit', '1'],
            ['Florida', 'FLA', 3, 'florida@tmlpstats.com', 'Florida', '16.1.3', 'America/New_York', '1'],
            ['Houston', 'HOU', 2, 'hou@tmlpstats.com', 'Houston', '16.1.3', 'America/Chicago', '1'],
            ['Montreal', 'MON', 3, 'montreal@tmlpstats.com', 'Montreal', '16.1.3', 'America/Montreal', '1'],
            ['New Jersey', 'NJ', 3, 'newjersey@tmlpstats.com', 'NJ', '16.1.3', 'America/New_York', '1'],
            ['New York', 'NY', 3, 'newyork@tmlpstats.com', 'NY', '16.1.3', 'America/New_York', '1'],
            ['Philadelphia', 'PA', 3, 'philadelphia@tmlpstats.com', 'PA', '16.1.3', 'America/New_York', '1'],
            ['Toronto', 'TOR', 3, 'toronto@tmlpstats.com', 'Toronto', '16.1.3', 'America/Toronto', '1'],
        ];
    }
}



