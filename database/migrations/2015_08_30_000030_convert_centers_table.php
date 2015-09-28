<?php

use TmlpStats\Center;
use TmlpStats\Region;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertCentersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('centers', function (Blueprint $table) {
            $table->integer('region_id')->unsigned()->after('team_name');
            $table->string('timezone')->after('sheet_version');
        });

        $centers = Center::all();
        foreach ($centers as $center) {
            $localRegion = null;
            $globalRegion = null;

            if ($center->globalRegion) {
                $globalRegion = Region::abbreviation($center->globalRegion)->first();
                if (!$globalRegion) {
                    $globalRegion = Region::create([
                        'abbreviation' => $center->globalRegion,
                        'name'         => $center->globalRegion,
                    ]);
                }
                $center->regionId = $globalRegion->id;
            }

            if ($center->localRegion) {
                $localRegion = Region::abbreviation($center->localRegion)->first();
                if (!$localRegion) {
                    $localRegion = Region::create([
                        'abbreviation' => $center->localRegion,
                        'name'         => $center->localRegion,
                    ]);
                }
                $localRegion->parentId = $globalRegion->id;
                $localRegion->save();

                $center->regionId = $localRegion->id;
            }
            $center->timezone = $center->timeZone; // make timezone name consistent with DateTime
            $center->save();
        }

        Schema::table('centers', function (Blueprint $table) {
            $table->dropColumn('global_region');
            $table->dropColumn('local_region');
            $table->dropColumn('time_zone');

            $table->foreign('region_id')->references('id')->on('regions');
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
