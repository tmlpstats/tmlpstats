<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddManagedToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('users', function (Blueprint $table) {
        //    $table->boolean('managed')->default(false)->after('active');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::table('users', function (Blueprint $table) {
        //    $table->dropColumn('managed');
        //});
    }
}
