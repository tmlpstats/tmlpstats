<?php

use TmlpStats\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RoleUserTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "role_user.csv";

    protected function createObject($data)
    {
        $user = User::find($data['user_id']);
        if ($user) {
            $user->roles()->attach($data['role_id']);
        }
    }
}

