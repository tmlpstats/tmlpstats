<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCentersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centers', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('abbreviation')->unique();
            $table->string('team_name')->nullable();
            $table->integer('region_id')->unsigned();
            $table->string('stats_email')->nullable();
            $table->boolean('active')->default(true);
            $table->string('sheet_filename');
            $table->string('sheet_version');
            $table->string('time_zone');
            $table->timestamps();
        });

        Schema::table('centers', function(Blueprint $table)
        {
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
        Schema::drop('centers');
    }

}
