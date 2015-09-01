<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('stats_report_id')->unsigned()->index();
            $table->integer('team_member_id')->unsigned();
            $table->boolean('at_weekend')->default(true);
            $table->boolean('xfer_out')->default(false);
            $table->boolean('xfer_in')->default(false);
            $table->boolean('ctw')->default(false);
            $table->integer('withdraw_code_id')->unsigned()->nullable();
            $table->boolean('rereg')->default(false);
            $table->boolean('excep')->default(false);
            $table->boolean('travel')->default(0);
            $table->boolean('room')->default(0);
            $table->string('comment')->nullable();
            $table->integer('accountability_id')->unsigned();
            $table->boolean('gitw')->default(0);
            $table->integer('tdo')->default(0);
            $table->timestamps();
        });

        Schema::table('team_members_data', function(Blueprint $table)
        {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->foreign('team_member_id')->references('id')->on('team_members');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
            $table->foreign('accountability_id')->references('id')->on('accountabilities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('team_members_data');
    }

}
