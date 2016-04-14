<?php

use TmlpStats\StatsReport;
use TmlpStats\CenterStats;
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
        //Schema::table('center_stats_data', function (Blueprint $table) {
        //    $table->integer('points')->nullable()->after('lf');
        //    $table->integer('program_manager_attending_weekend')->unsigned()->default(0)->after('points');
        //    $table->integer('classroom_leader_attending_weekend')->unsigned()->default(0)->after('program_manager_attending_weekend');
        //});
        //
        //$deleted = 0;
        //$centerStatsData = CenterStatsData::all();
        //foreach ($centerStatsData as $data) {
        //
        //    // We're only copying data for actual data rows
        //    if ($data->type !== 'actual') {
        //        continue;
        //    }
        //
        //    $centerStats = CenterStats::actual($data)->first();
        //    if (!$centerStats) {
        //        // DATA PRUNING: Drop rows that aren't paired with a report
        //        $deleted++;
        //        $data->delete();
        //        continue;
        //    }
        //
        //    $statsReport = StatsReport::find($centerStats->statsReportId);
        //    if (!$statsReport) {
        //        // DATA PRUNING: Drop rows that aren't paired with a report
        //        $deleted++;
        //        $data->delete();
        //        continue;
        //    }
        //
        //    if ($data->rating && preg_match("/\((\d+)\)/", $data->rating, $matches)) {
        //        $data->points = $matches[1];
        //    }
        //
        //    $data->programManagerAttendingWeekend = $statsReport->programManagerAttendingWeekend;
        //    $data->classroomLeaderAttendingWeekend = $statsReport->classroomLeaderAttendingWeekend;
        //    $data->save();
        //}
        //if ($deleted) {
        //    $total = $centerStatsData->count();
        //    echo "Removing {$deleted}/{$total} entries from CenterStatsData\n";
        //}

        //Schema::table('stats_reports', function (Blueprint $table) {
        //    $table->dropColumn('program_manager_attending_weekend');
        //    $table->dropColumn('classroom_leader_attending_weekend');
        //    $table->dropColumn('center_stats_id');
        //});

        //Schema::table('center_stats_data', function (Blueprint $table) {
        //    $table->dropIndex('center_stats_data_center_id_foreign');
        //    $table->dropIndex('center_stats_data_quarter_id_foreign');
        //
        //    $table->dropColumn('rating');
        //    $table->dropColumn('offset');
        //    $table->dropColumn('center_id');
        //    $table->dropColumn('quarter_id');
        //});

        //Schema::table('center_stats', function (Blueprint $table) {
        //    $table->dropIndex('center_stats_center_id_foreign');
        //    $table->dropIndex('center_stats_quarter_id_foreign');
        //
        //    $table->dropColumn('quarter_id');
        //    $table->dropColumn('center_id');
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
