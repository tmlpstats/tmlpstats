<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTeamForeignKeyToInterestFormTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('interest_forms', function (Blueprint $table) {
            $table->integer('team_id')->nullable()->unsigned();

            $table->foreign('team_id')->references('id')->on('centers');

            $table->dropColumn('team');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('interest_forms', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->string('team');
        });
    }
}
