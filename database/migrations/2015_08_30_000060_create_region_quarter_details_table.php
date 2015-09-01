<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionQuarterDetailsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('region_quarter_details', function (Blueprint $table) {
            $table->integer('quarter_id')->unsigned()->index();
            $table->integer('region_id')->unsigned()->index();
            $table->string('location', 128);
            $table->date('start_weekend_date')->nullable();
            $table->date('end_weekend_date')->nullable();
            $table->date('classroom1_date')->nullable();
            $table->date('classroom2_date')->nullable();
            $table->date('classroom3_date')->nullable();
            $table->timestamps();
        });

        Schema::table('region_quarter_details', function (Blueprint $table) {
            $table->unique(array('quarter_id', 'region_id', 'start_weekend_date'), 'region_quarter_start_weekend_date_unique');

            $table->foreign('quarter_id')->references('id')->on('quarters');
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
        Schema::drop('region_quarter_details');
    }

}
