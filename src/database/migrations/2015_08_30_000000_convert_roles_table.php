<?php

use TmlpStats\Role;
use TmlpStats\Util;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertRolesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('roles', function (Blueprint $table) {
        //    $table->string('display')->after('name');
        //});
        //
        //$roles = Role::all();
        //foreach ($roles as $role) {
        //    $role->display = ucwords(Util::toWords($role->name));
        //    $role->save();
        //}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::table('roles', function (Blueprint $table) {
        //    $table->dropColumn('display');
        //});
    }

}
