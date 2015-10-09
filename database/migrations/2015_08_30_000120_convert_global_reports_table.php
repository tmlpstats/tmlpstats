<?php

use Carbon\Carbon;
use TmlpStats\Center;
use TmlpStats\CenterStats;
use TmlpStats\CenterStatsData;
use TmlpStats\GlobalReport;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use TmlpStats\StatsReport;

class ConvertGlobalReportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('global_reports', function (Blueprint $table) {
            $table->index('reporting_date');
            $table->dropColumn('quarter_id');
        });

        $date = new Carbon('last friday');
        $quarterStart = Carbon::create(2015, 8, 21);

        while ($date->gt($quarterStart)) {
            $globalReport = GlobalReport::create(array(
                'reporting_date' => $date->toDateString(),
            ));

            // Make sure CenterStatsData rows reference the correct statsReport
            $centers = Center::all();
            foreach ($centers as $center) {
                $statsReport = StatsReport::byCenter($center)
                    ->reportingDate($date)
                    ->submitted()
                    ->orderBy('submitted_at', 'desc')
                    ->first();

                if ($statsReport) {
                    $globalReport->statsReports()->attach($statsReport->id);

                    $centerStats = CenterStats::find($statsReport->centerStatsId);
                    if ($centerStats) {
                        if ($date->toDateString() === '2015-08-28') {

                            $promises = CenterStatsData::promise()
                                ->byCenter($statsReport->center)
                                ->byQuarter($statsReport->quarter)
                                ->get();

                            foreach ($promises as $promise) {
                                $promise->statsReportId = $statsReport->id;
                                $promise->save();
                            }
                        }
                        $actual = $centerStats->actualData;
                        $actual->statsReportId = $statsReport->id;
                        $actual->save();
                    }
                }
            }

            $date->subWeek();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('global_reports', function (Blueprint $table) {
            $table->dropIndex('reporting_date');
            $table->integer('quarter_id')->unsigned();
        });
    }

}
