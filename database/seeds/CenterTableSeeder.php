<?php

use TmlpStats\Center;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CenterTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        $centers = array(
            array('name' => 'Vancouver','sheet_filename' => 'Vancouver','abbreviation' => 'VAN','global_region' => 'NA','local_region' => 'West','stats_email' => 'vancouver.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Boston','sheet_filename' => 'Boston','abbreviation' => 'BOS','global_region' => 'NA','local_region' => 'East','stats_email' => 'boston.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Los Angeles','sheet_filename' => 'LA','abbreviation' => 'LA','global_region' => 'NA','local_region' => 'West','stats_email' => 'la.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'MSP','sheet_filename' => 'MSP','abbreviation' => 'MSP','global_region' => 'NA','local_region' => 'West','stats_email' => 'msp.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Orange County','sheet_filename' => 'OC','abbreviation' => 'OC','global_region' => 'NA','local_region' => 'West','stats_email' => 'orangecounty.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Phoenix','sheet_filename' => 'Phoenix','abbreviation' => 'PHO','global_region' => 'NA','local_region' => 'West','stats_email' => 'phoenix.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'San Diego','sheet_filename' => 'SD','abbreviation' => 'SD','global_region' => 'NA','local_region' => 'West','stats_email' => 'sandiego.tmlpstats@gmail.com', 'active' => false),
            array('name' => 'Seattle','sheet_filename' => 'Seattle','abbreviation' => 'SEA','global_region' => 'NA','local_region' => 'West','stats_email' => 'seattle.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'San Francisco','sheet_filename' => 'SF','abbreviation' => 'SF','global_region' => 'NA','local_region' => 'West','stats_email' => 'sanfrancisco.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'San Jose','sheet_filename' => 'SJ','abbreviation' => 'SJ','global_region' => 'NA','local_region' => 'West','stats_email' => 'sanjose.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Washington, DC','sheet_filename' => 'WDC','abbreviation' => 'WDC','global_region' => 'NA','local_region' => 'West','stats_email' => 'washingtondc.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Mexico','sheet_filename' => 'Mexico','abbreviation' => 'MEX','global_region' => 'NA','local_region' => 'West','stats_email' => 'mexico.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Atlanta','sheet_filename' => 'Atlanta','abbreviation' => 'ATL','global_region' => 'NA','local_region' => 'East','stats_email' => 'atlanta.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Chicago','sheet_filename' => 'Chicago','abbreviation' => 'CHI','global_region' => 'NA','local_region' => 'East','stats_email' => 'chicago.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Dallas','sheet_filename' => 'Dallas','abbreviation' => 'DFW','global_region' => 'NA','local_region' => 'East','stats_email' => 'dfw.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Denver','sheet_filename' => 'Denver','abbreviation' => 'DEN','global_region' => 'NA','local_region' => 'East','stats_email' => 'den.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Detroit','sheet_filename' => 'Detroit','abbreviation' => 'DET','global_region' => 'NA','local_region' => 'East','stats_email' => 'detroit.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Florida','sheet_filename' => 'Florida','abbreviation' => 'FLA','global_region' => 'NA','local_region' => 'East','stats_email' => 'florida.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Houston','sheet_filename' => 'Houston','abbreviation' => 'HOU','global_region' => 'NA','local_region' => 'East','stats_email' => 'hou.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Montreal','sheet_filename' => 'Montreal','abbreviation' => 'MON','global_region' => 'NA','local_region' => 'East','stats_email' => 'montreal.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'New Jersey','sheet_filename' => 'NJ','abbreviation' => 'NJ','global_region' => 'NA','local_region' => 'East','stats_email' => 'newjersey.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'New York','sheet_filename' => 'NY','abbreviation' => 'NY','global_region' => 'NA','local_region' => 'East','stats_email' => 'newyork.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Philadelphia','sheet_filename' => 'PA','abbreviation' => 'PA','global_region' => 'NA','local_region' => 'East','stats_email' => 'philadelphia.tmlpstats@gmail.com', 'active' => true),
            array('name' => 'Toronto','sheet_filename' => 'Toronto','abbreviation' => 'TOR','global_region' => 'NA','local_region' => 'East','stats_email' => 'toronto.tmlpstats@gmail.com', 'active' => true)
        );

        foreach ($centers as $center) {
            $newCenter = $center;
            $newCenter['team_name'] = null;
            $newCenter['sheet_version'] = '15.5';
            Center::create($newCenter);
        }
    }
}
