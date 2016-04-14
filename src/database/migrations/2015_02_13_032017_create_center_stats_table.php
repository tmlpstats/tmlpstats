<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCenterStatsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::create('center_stats', function(Blueprint $table)
        //{
        //    $table->increments('id');
        //    $table->date('reporting_date');
        //    $table->integer('promise_data_id')->unsigned()->nullable();
        //    $table->integer('revoked_promise_data_id')->unsigned()->nullable();
        //    $table->integer('actual_data_id')->unsigned()->nullable();
        //    $table->integer('center_id')->unsigned();
        //    $table->integer('quarter_id')->unsigned();
        //    // Not adding a foreign key because there's a circular reference. Adding stats_report
        //    // to make it easier to delete all data added by a stats_report if it fails validation
        //    $table->integer('stats_report_id')->unsigned()->nullable();
        //    $table->timestamps();
        //});
        //
        //Schema::table('center_stats', function(Blueprint $table)
        //{
        //    $table->foreign('center_id')->references('id')->on('centers');
        //    $table->foreign('quarter_id')->references('id')->on('quarters');
        //    $table->foreign('promise_data_id')->references('id')->on('center_stats_data');
        //    $table->foreign('revoked_promise_data_id')->references('id')->on('center_stats_data');
        //    $table->foreign('actual_data_id')->references('id')->on('center_stats_data');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::drop('center_stats');
    }
}
