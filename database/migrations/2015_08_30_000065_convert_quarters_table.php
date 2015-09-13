<?php

use Carbon\Carbon;
use TmlpStats\Quarter;
use TmlpStats\Region;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertQuartersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quarters', function (Blueprint $table) {
            $table->string('t1_distinction')->after('id');
            $table->string('t2_distinction')->after('t1_distinction');
            $table->integer('quarter_number')->after('t2_distinction');
            $table->integer('year')->after('quarter_number');

            $table->boolean('delete_me')->default(false);

            $table->dropUnique(array('global_region', 'local_region', 'start_weekend_date'));
//            $table->unique(array('quarter_number','year'));
        });

        $quarters = Quarter::all();
        foreach ($quarters as $quarter) {
            $region = $quarter->localRegion
                ? Region::abbreviation($quarter->localRegion)->first()
                : Region::abbreviation($quarter->globalRegion)->first();

            $quarterRegionId = DB::table('quarter_region')->insertGetId([
                'quarter_id'         => $quarter->id,
                'region_id'          => $region->id,
                'location'           => $quarter->location,
                'start_weekend_date' => $quarter->startWeekendDate,
                'end_weekend_date'   => $quarter->endWeekendDate,
                'classroom1_date'    => $quarter->classroom1Date,
                'classroom2_date'    => $quarter->classroom2Date,
                'classroom3_date'    => $quarter->classroom3Date,
                'created_at'         => DB::raw('NOW()'),
                'updated_at'         => DB::raw('NOW()'),
            ]);

            $quarter->t1Distinction = $quarter->distinction;

            $startDate = $quarter->startWeekendDate;
            if ($startDate) {
                if (!($startDate instanceof Carbon)) {
                    $startDate = Carbon::createFromFormat('Y-m-d', $startDate);
                }
                switch ($startDate->month) {
                    case 1:
                    case 2:
                    case 3:
                        $quarter->quarterNumber = 1;
                        break;
                    case 4:
                    case 5:
                    case 6:
                        $quarter->quarterNumber = 2;
                        break;
                    case 7:
                    case 8:
                    case 9:
                        $quarter->quarterNumber = 3;
                        break;
                    case 10:
                    case 11:
                    case 13:
                        $quarter->quarterNumber = 4;
                        break;
                }
                $quarter->year = $startDate->year;
            }

            $newQuarter = Quarter::year($quarter->year)
                ->quarterNumber($quarter->quarterNumber)
                ->first();
            if ($newQuarter && $newQuarter->id != $quarter->id) {
                DB::table('quarter_region')
                    ->where('quarter_id', $quarter->id)
                    ->where('region_id', $region->id)
                    ->update(array('quarter_id' => $newQuarter->id));
                // TODO: delete any deleteMes
                $quarter->deleteMe = true;
            }
            $quarter->save();
        }

        $backQuarters = array(
            array('Completion', 1, 2013),
            array('Relatedness', 2, 2013),
            array('Possibility', 3, 2013),
            array('Opportunity', 4, 2013),
            array('Action', 1, 2014),
        );
        foreach ($backQuarters as $quarter) {
            Quarter::create([
                't1_distinction' => $quarter[0],
                'quarter_number' => $quarter[1],
                'year'           => $quarter[2],
            ]);
        }

        Schema::table('quarters', function (Blueprint $table) {
            $table->dropColumn('distinction');
            $table->dropColumn('location');
            $table->dropColumn('start_weekend_date');
            $table->dropColumn('end_weekend_date');
            $table->dropColumn('classroom1_date');
            $table->dropColumn('classroom2_date');
            $table->dropColumn('classroom3_date');
            $table->dropColumn('global_region');
            $table->dropColumn('local_region');
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
