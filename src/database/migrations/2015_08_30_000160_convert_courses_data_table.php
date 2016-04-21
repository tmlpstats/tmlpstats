<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertCoursesDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('courses_data', function (Blueprint $table) {
        //    $table->dropIndex('courses_data_center_id_foreign');
        //    $table->dropIndex('courses_data_quarter_id_foreign');
        //
        //    $table->dropColumn('reporting_date');
        //    $table->dropColumn('offset');
        //    $table->dropColumn('center_id');
        //    $table->dropColumn('quarter_id');
        //});
        //
        //Schema::table('courses_data', function (Blueprint $table) {
        //    $table->index('stats_report_id');
        //    $table->foreign('stats_report_id')->references('id')->on('stats_reports');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // from backup
    }

}
