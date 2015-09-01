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
        Schema::table('courses_data', function (Blueprint $table) {
            $table->dropForeign('center_id');
            $table->dropForeign('quarter_id');

            $table->dropColumn('reporting_date');
            $table->dropColumn('offset');
            $table->dropColumn('center_id');
            $table->dropColumn('quarter_id');
        });

        Schema::table('courses_data', function (Blueprint $table) {
            $table->integer('stats_report_id')->unsigned()->index();
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
        // from backup
    }

}
