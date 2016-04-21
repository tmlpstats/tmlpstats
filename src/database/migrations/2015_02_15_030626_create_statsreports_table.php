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
        Schema::create('stats_reports', function(Blueprint $table) {
            $table->increments('id');
            $table->date('reporting_date');
            $table->string('version');
            $table->boolean('validated')->default(false);
            $table->integer('reporting_statistician_id')->unsigned()->nullable();
            $table->integer('center_id')->unsigned();
            $table->integer('quarter_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('locked')->tinyInteger()->default(0);
            $table->timestamp('submitted_at')->nullable()->default(null);
            $table->string('submit_comment', 8096)->nullable()->default(null);
            $table->timestamps();

            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('quarter_id')->references('id')->on('quarters');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('reporting_date');
        });

        Schema::table('team_members_data', function (Blueprint $table) {
            $table->integer('stats_report_id')->unsigned()->nullable()->index()->after('tdo');
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
        });

        Schema::table('courses_data', function (Blueprint $table) {
            $table->integer('stats_report_id')->unsigned()->nullable()->after('guests_attended');
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->index('stats_report_id');
        });

        Schema::table('tmlp_games_data', function (Blueprint $table) {
            $table->integer('stats_report_id')->unsigned()->nullable()->after('quarter_start_approved');
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->index('stats_report_id');
        });

        Schema::table('tmlp_registrations_data', function (Blueprint $table) {
            $table->integer('stats_report_id')->unsigned()->nullable()->after('room');
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->index('stats_report_id');
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
