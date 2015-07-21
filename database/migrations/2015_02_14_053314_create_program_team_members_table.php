<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgramTeamMembersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('program_team_members', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('team_member_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('offset');
            $table->string('accountability');
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('center_id')->unsigned();
            $table->integer('quarter_id')->unsigned();
            $table->integer('stats_report_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('program_team_members', function(Blueprint $table)
        {
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('quarter_id')->references('id')->on('quarters');
            $table->foreign('team_member_id')->references('id')->on('team_members');
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
        Schema::drop('program_team_members');
    }

}
