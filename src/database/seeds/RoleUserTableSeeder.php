<?php

use TmlpStats\User;
use TmlpStats\Seeders\CsvSeederAbstract;

class RoleUserTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "role_user.csv";

    protected function createObject($data)
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->roles()->attach($data['role_id']);
        }
    }
}

