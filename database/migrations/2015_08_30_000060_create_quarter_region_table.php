<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuarterRegionTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quarter_region', function (Blueprint $table) {
            $table->integer('quarter_id')->unsigned()->index();
            $table->integer('region_id')->unsigned()->index();
            $table->string('location', 128);
            $table->date('start_weekend_date')->nullable();
            $table->date('end_weekend_date')->nullable();
            $table->date('classroom1_date')->nullable();
            $table->date('classroom2_date')->nullable();
            $table->date('classroom3_date')->nullable();
            $table->timestamps();

            $table->unique(array('quarter_id', 'region_id', 'start_weekend_date'));
        });

        Schema::table('quarter_region', function (Blueprint $table) {
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
        Schema::drop('quarter_region');
    }

}
