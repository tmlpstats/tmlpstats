<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubmitCommentToStatsReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('stats_reports', function(Blueprint $table)
        //{
        //    $table->string('submit_comment', 8096)->nullable()->default(null);
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::table('stats_reports', function(Blueprint $table)
        //{
        //    $table->dropColumn('submit_comment');
        //});
    }
}
