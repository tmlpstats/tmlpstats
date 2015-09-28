<?php

use Carbon\Carbon;
use TmlpStats\Quarter;
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

            $quarter = Quarter::region($report->center->region)
                ->date($report->reportingDate)
                ->first();
            $report->quarterId = $quarter->id;
            $report->save();

            if (!$report->centerStatsId) {
                continue;
            }

            $centerStatsId = $report->centerStatsId;
            $centerStats = CenterStats::find($centerStatsId);
            if (!$centerStats) {
                continue;
            } else {
                $centerStats->statsReportId = $report->id;
                $centerStats->save();
            }

            $activeCenterStats[] = $centerStatsId;
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

            $table->dropColumn('center_stats_id');
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
