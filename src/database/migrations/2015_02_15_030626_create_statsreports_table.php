<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsreportsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats_reports', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date('reporting_date');
            $table->string('spreadsheet_version');
            $table->integer('center_stats_id')->unsigned()->nullable();
            $table->boolean('validated')->default(false);
            $table->integer('reporting_statistician_id')->unsigned()->nullable();
            $table->string('program_manager_attending_weekend')->default(false);
            $table->string('classroom_leader_attending_weekend')->default(false);
            $table->integer('center_id')->unsigned();
            $table->integer('quarter_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('stats_reports', function(Blueprint $table)
        {
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('quarter_id')->references('id')->on('quarters');
            $table->foreign('center_stats_id')->references('id')->on('center_stats');
            $table->foreign('reporting_statistician_id')->references('id')->on('program_team_members');
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
        Schema::drop('stats_reports');
    }

}
