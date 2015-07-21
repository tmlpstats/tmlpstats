<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalReportStatsReportTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_report_stats_report', function(Blueprint $table)
        {
            $table->integer('stats_report_id')->unsigned()->index();
            $table->integer('global_report_id')->unsigned()->index();

            $table->timestamps();

            $table->foreign('stats_report_id')->references('id')->on('stats_reports')->onDelete('cascade');
            $table->foreign('global_report_id')->references('id')->on('global_reports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('global_report_stats_report');
    }

}
