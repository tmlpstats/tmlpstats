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

            $table->timestamp('created_at_tmp')->after('submit_comment');
            $table->timestamp('updated_at_tmp')->after('created_at_tmp');
        });

        foreach (StatsReport::all() as $report) {
            $report->createdAtTmp = $report->createdAt;
            $report->updatedAtTmp = $report->updatedAt;
            $report->save();
        }

        Schema::table('stats_reports', function (Blueprint $table) {
            $table->dropIndex('stats_reports_center_stats_id_foreign');
            $table->dropIndex('stats_reports_reporting_statistician_id_foreign');

            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
            $table->renameColumn('created_at_tmp', 'created_at');
            $table->renameColumn('updated_at_tmp', 'updated_at');
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
