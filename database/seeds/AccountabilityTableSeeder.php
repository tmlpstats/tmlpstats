<?php

use TmlpStats\Accountability;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AccountabilityTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        $accountabilities = array(
            'regionalStatistician'       => 'program',
            'globalStatistician'         => 'program',
            'globalLeader'               => 'program',
            'teamStatistician'           => 'team',
            'teamStatisticianApprentice' => 'team',
            'team1TeamLeader'            => 'team',
            'team2TeamLeader'            => 'team',
            'programManager'             => 'team',
            'classroomLeader'            => 'team',
        );

        foreach ($accountabilities as $name => $context) {
            $accountability = array(
                'name' => $name,
                'context' => $context,
            );
            Accountability::create($accountability);
        }
    }
}
