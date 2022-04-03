<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusToStatsReportsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats_reports', function (Blueprint $table) {
            $table->boolean('reviewed')->default(false);
            $table->dateTime('reviewed_at')->nullable();
            $table->integer('reviewed_by')->unsigned()->nullable();
            $table->boolean('approved')->default(false);
            $table->dateTime('approved_at')->nullable();
            $table->integer('approved_by')->unsigned()->nullable();

            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stats_reports', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
            $table->dropForeign(['approved_by']);

            $table->dropColumn(['reviewed', 'reviewed_at', 'reviewed_by', 'approved', 'approved_at', 'approved_by']);
        });
    }
}
