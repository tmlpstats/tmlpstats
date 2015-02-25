<?php

use TmlpStats\Role;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class RoleTableSeeder extends Seeder {

    public function run()
    {
        Model::unguard();

        $roles = array(
            'administrator',
            'globalStatistician',
            'programLeader',
            'programMember',
        );

        foreach ($roles as $name) {
            $role = array(
                'name' => $name,
            );
            Role::create($role);
        }
    }
}
