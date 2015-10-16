<?php

use TmlpStats\Role;
use TmlpStats\User;
use Illuminate\Database\Seeder;

class DefaultAdminSeeder extends Seeder {

    public function run()
    {
        $user = User::create([
            'email' => 'admin@tmlpstats.com',
            'password' => Hash::make('password'),
            'first_name' => 'Joe',
            'last_name' => 'Admin',
            'phone' => '555-555-5555',
        ]);

        if (!Role::find(1)) {
            echo "Admin role not found. Did you provide an export of the database?";
        }

        $user->roles()->attach(1); // Admin
        $user->roles()->attach(2); // Global Statistician
        $user->centers()->attach(1); // Vancouver

        $user->save();
    }
}
