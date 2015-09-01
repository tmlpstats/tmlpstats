<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpRegistrationsDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmlp_registrations_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('stats_report_id')->unsigned()->index();
            $table->integer('tmlp_registration_id')->unsigned();
            $table->date('reg_date')->nullable();
            $table->date('app_out_date')->nullable();
            $table->date('app_in_date')->nullable();
            $table->date('appr_date')->nullable();
            $table->date('wd_date')->nullable();
            $table->integer('withdraw_code_id')->unsigned()->nullable();
            $table->integer('committed_team_member_id')->unsigned()->nullable();
            $table->string('comment')->nullable();
            $table->integer('incoming_quarter_id')->unsigned();
            $table->boolean('travel')->default(0);
            $table->boolean('room')->default(0);
            $table->timestamps();
        });

        Schema::table('tmlp_registrations_data', function(Blueprint $table)
        {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->foreign('tmlp_registration_id')->references('id')->on('tmlp_registrations');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
            $table->foreign('committed_team_member_id')->references('id')->on('team_members');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tmlp_registrations_data');
    }

}
