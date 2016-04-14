<?php

use Illuminate\Database\Seeder;
use TmlpStats as Models;
use TmlpStats\Http\Controllers\Auth\AuthController;

class PeopleObjectSeeder extends Seeder
{
    public function run()
    {
        //if (!Schema::hasTable('users')
        //    || !Schema::hasTable('people')
        //    || !Schema::hasTable('roles')
        //    || !Schema::hasTable('centers')
        //    || !Models\Role::find(1)
        //    || !Models\Center::find(1)
        //) {
        //    echo "Unable to find basic database objects. Make sure you import the database first.\n\n";
        //
        //    return false;
        //}
        //
        //$user = App::make(AuthController::class)->create([
        //    'email'      => 'admin@tmlpstats.com',
        //    'password'   => 'password',
        //    'first_name' => 'Joe',
        //    'last_name'  => 'Admin',
        //    'phone'      => '555-555-5555',
        //]);
        //
        //$vancouver = Models\Center::find(1);
        //$roleAdmin = Models\Role::name('administrator')->first();
        //
        //
        //$user->roleId = 1; // Admin
        //
        //$user->setCenter(Models\Center::find(1)); // Vancouver
        //
        //$user->save();
        //
        //
        //$personTeamMember = Models\Person::create([
        //    'first_name' => 'Alex',
        //    'last_name'  => 'Tester',
        //    'phone'      => '555-555-5555',
        //    'email'      => 'tester@tmlpstats.com',
        //    'center_id'  => $vancouver->id,
        //    'identifier' => 'test_user',
        //]);
        //
        //$this->teamMember = Models\TeamMember::firstOrCreate([
        //    'person_id'           => $person->id,
        //    'team_year'           => 1,
        //    'incoming_quarter_id' => $this->lastQuarter->id,
        //]);
        //
        //$this->user = Models\User::firstOrCreate([
        //    'email'         => $person->email,
        //    'password'      => 'x',
        //    'person_id'     => $person->id,
        //    'role_id'       => $roleAdmin->id,
        //    'last_login_at' => Carbon::now()->toDateTimeString(),
        //]);
    }
}
