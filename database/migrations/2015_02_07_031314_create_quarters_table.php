<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuartersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quarters', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('location', 128);
            $table->string('distinction');
            $table->date('start_weekend_date');
            $table->date('end_weekend_date');
            $table->date('classroom1_date');
            $table->date('classroom2_date');
            $table->date('classroom3_date');
            $table->timestamps();

            $table->string('global_region', 64);
            $table->string('local_region', 64);

            $table->unique(array('global_region','local_region','start_weekend_date'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('quarters');
    }

}
