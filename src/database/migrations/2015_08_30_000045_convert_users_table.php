<?php

use TmlpStats\User;
use TmlpStats\Person;
use TmlpStats\Center;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('users', function (Blueprint $table) {
        //    $table->integer('person_id')->unsigned()->index()->nullable()->after('active');
        //    $table->integer('role_id')->unsigned()->nullable()->after('person_id');
        //
        //    $table->foreign('person_id')->references('id')->on('people');
        //    $table->foreign('role_id')->references('id')->on('roles');
        //});
        //
        //$users = User::all();
        //foreach ($users as $user) {
        //    $centerUser = DB::table('center_user')->where('user_id', '=', $user->id)->first();
        //
        //    $person = Person::create([
        //        'first_name' => $user->firstName,
        //        'last_name'  => $user->lastName,
        //        'phone'      => $user->phone,
        //        'email'      => $user->email,
        //        'center_id'  => $centerUser ? $centerUser->center_id : null,
        //    ]);
        //
        //    $isAdmin = DB::table('role_user')
        //        ->where('user_id', '=', $user->id)
        //        ->where('role_id', '=', 1)
        //        ->first();
        //    $isGlobal = DB::table('role_user')
        //        ->where('user_id', '=', $user->id)
        //        ->where('role_id', '=', 1)
        //        ->first();
        //    if ($isAdmin) {
        //        $user->roleId = 1;
        //    } else if ($isGlobal) {
        //        $user->roleId = 2;
        //    } else {
        //        $user->roleId = 3;
        //    }
        //    $user->personId = $person->id;
        //    $user->save();
        //}
        //
        //Schema::table('users', function (Blueprint $table) {
        //    $table->dropColumn('first_name');
        //    $table->dropColumn('last_name');
        //    $table->dropColumn('phone');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // from backup
    }

}
