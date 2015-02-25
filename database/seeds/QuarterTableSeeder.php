<?php

use TmlpStats\Quarter;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class QuarterTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        $quarters = array(
            array('start_weekend_date' => '2014-05-30','end_weekend_date' => '2014-08-15','classroom1_date' => '2014-06-20','classroom2_date' => '2014-07-11','classroom3_date' => '2014-08-01','location' => 'Houston','distinction' => 'Completion'),
            array('start_weekend_date' => '2014-08-15','end_weekend_date' => '2014-11-14','classroom1_date' => '2014-08-29','classroom2_date' => '2014-09-19','classroom3_date' => '2014-10-24','location' => 'Seattle','distinction' => 'Relatedness'),
            array('start_weekend_date' => '2014-11-14','end_weekend_date' => '2015-02-20','classroom1_date' => '2014-12-05','classroom2_date' => '2015-01-09','classroom3_date' => '2015-02-06','location' => 'Minneapolis','distinction' => 'Possibility'),
            array('start_weekend_date' => '2015-02-20','end_weekend_date' => '2015-05-29','classroom1_date' => '2015-03-13','classroom2_date' => '2015-04-17','classroom3_date' => '2015-05-08','location' => 'Atlanta','distinction' => 'Opportunity'),
            array('start_weekend_date' => '2015-05-29','end_weekend_date' => '2015-08-21','classroom1_date' => '2015-06-19','classroom2_date' => '2015-07-10','classroom3_date' => '2015-07-31','location' => 'Seattle','distinction' => 'Action'),
            array('start_weekend_date' => '2015-08-21','end_weekend_date' => '2015-11-20','classroom1_date' => '2015-09-11','classroom2_date' => '2015-10-09','classroom3_date' => '2015-11-06','location' => 'Orange County','distinction' => 'Completion'),
            array('start_weekend_date' => '2015-11-20','end_weekend_date' => '2016-02-19','classroom1_date' => '2015-12-04','classroom2_date' => '2016-01-08','classroom3_date' => '2016-02-05','location' => 'TBD','distinction' => 'Relatedness')
        );

        foreach ($quarters as $quarter) {
            Quarter::create($quarter);
        }
    }
}
