<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCenterStatsDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('center_stats_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('stats_report_id')->unsigned()->index();
            $table->string('type'); // promise/actual
            $table->integer('cap');
            $table->integer('cpc');
            $table->integer('t1x');
            $table->integer('t2x');
            $table->integer('gitw');
            $table->integer('lf');
            $table->integer('tdo');
            $table->integer('points');
            $table->integer('program_manager_attending_weekend')->unsigned()->default(0);
            $table->integer('classroom_leader_attending_weekend')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::table('center_stats_data', function(Blueprint $table)
        {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('center_stats_data');
    }
}
