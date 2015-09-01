<?php

use TmlpStats\StatsReport;
use TmlpStats\CenterStatsData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertCenterStatsDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('center_stats_data', function (Blueprint $table) {
            $table->integer('points');
            $table->integer('program_manager_attending_weekend')->unsigned()->default(0);
            $table->integer('classroom_leader_attending_weekend')->unsigned()->default(0);
        });

        $centerStatsData = CenterStatsData::all();
        foreach ($centerStatsData as $data) {
            $statsReport = StatsReport::find($data->statsReportId);
            if (!$statsReport) {
                // DATA PRUNING: Drop rows that aren't paired with a report
                $data->delete();
            }

            if ($data->rating && preg_match("/\((\d+)\)/", $data->rating, $matches)) {
                $data->points = $matches[1];
            }

            $data->programManagerAttendingWeekend = $statsReport->programManagerAttendingWeekend;
            $data->classroomLeaderAttendingWeekend = $statsReport->classroomLeaderAttendingWeekend;
            $data->save();
        }

        Schema::table('stats_reports', function (Blueprint $table) {
            $table->dropColumn('program_manager_attending_weekend');
            $table->dropColumn('classroom_leader_attending_weekend');
        });

        Schema::table('center_stats_data', function (Blueprint $table) {
            // Do this after cleaning up the data
            $table->integer('stats_report_id')->index();
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');

            $table->dropForeign('center_id');
            $table->dropForeign('quarter_id');

            $table->dropColumn('rating');
            $table->dropColumn('offset');
            $table->dropColumn('center_id');
            $table->dropColumn('quarter_id');
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
