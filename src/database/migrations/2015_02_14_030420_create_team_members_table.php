<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('team_year');
            $table->string('accountability')->nullable();
            $table->integer('completion_quarter_id')->unsigned();
            $table->integer('center_id')->unsigned();
            $table->integer('stats_report_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('team_members', function(Blueprint $table)
        {
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('completion_quarter_id')->references('id')->on('quarters');
            $table->foreign('user_id')->references('id')->on('users');
            // Not adding a foreign key for stats_reports because there's a circular reference. Adding stats_report
            // to make it easier to delete all data added by a stats_report if it fails validation
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('team_members');
    }

}
