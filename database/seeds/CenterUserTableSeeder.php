<?php

use TmlpStats\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class CenterUserTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "center_user.csv";

    protected function createObject($data)
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->centers()->attach($data['center_id']);
        }
    }
}

