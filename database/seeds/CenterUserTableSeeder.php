<?php

use TmlpStats\User;
use TmlpStats\Seeders\CsvSeederAbstract;

class CenterUserTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "center_user.csv";

    protected function createObject($data)
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->centers()->attach($data['center_id']);
        }
    }
}

