<?php

use TmlpStats\CenterStats;
use TmlpStats\StatsReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertStatsReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stats_reports', function (Blueprint $table) {
            $table->index('reporting_date');
            $table->renameColumn('spreadsheet_version', 'version');
        });

        // DATA PRUNING: Remove unreferenced CenterStats objects
        $activeCenterStats = array();
        foreach (StatsReport::all() as $report) {
            if (!$report->centerStatsId) {
                continue;
            }
            $activeCenterStats[] = $report->centerStatsId;
        }
        $total = DB::table('center_stats')->count();
        $query = DB::table('center_stats')->whereNotIn('id', $activeCenterStats);
        echo "Removing " . $query->count() . "/{$total} entries from CenterStats\n";
        $query->delete();

        $centerStatsList = DB::table('center_stats')->get();

        // DATA PRUNING: Remove unreferenced CenterStats objects
        $activeCenterStatsData = array();
        foreach ($centerStatsList as $centerStats) {
            if ($centerStats->promise_data_id) {
                $activeCenterStatsData[] = $centerStats->promise_data_id;
            }
            if ($centerStats->revoked_promise_data_id) {
                $activeCenterStatsData[] = $centerStats->revoked_promise_data_id;
            }
            if ($centerStats->actual_data_id) {
                $activeCenterStatsData[] = $centerStats->actual_data_id;
            }
        }
        $total = DB::table('center_stats_data')->count();
        $query = DB::table('center_stats_data')->whereNotIn('id', $activeCenterStatsData);
        echo "Removing " . $query->count() . "/{$total} entries from CenterStatsData\n";
        $query->delete();

        Schema::table('stats_reports', function (Blueprint $table) {
            $table->dropIndex('stats_reports_center_stats_id_foreign');
            $table->dropIndex('stats_reports_reporting_statistician_id_foreign');
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
