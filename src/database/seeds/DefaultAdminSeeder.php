<?php

use TmlpStats\Center;
use TmlpStats\Role;
use TmlpStats\Http\Controllers\Auth\AuthController;
use Illuminate\Database\Seeder;

class DefaultAdminSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('users')
            || !Schema::hasTable('people')
            || !Schema::hasTable('roles')
            || !Schema::hasTable('centers')
            || !Role::find(1)
            || !Center::find(1)
        ) {
            echo "Unable to find basic database objects. Make sure you import the database first.\n\n";
            return false;
        }

        $user = App::make(AuthController::class)->create([
            'email'      => 'admin@tmlpstats.com',
            'password'   => 'password',
            'first_name' => 'Joe',
            'last_name'  => 'Admin',
            'phone'      => '555-555-5555',
        ]);

        $user->roleId = 1; // Admin
        $user->setCenter(Center::find(1)); // Vancouver

        $user->save();
    }
}
