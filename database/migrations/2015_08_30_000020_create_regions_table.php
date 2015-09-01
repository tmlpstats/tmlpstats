<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regions', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('abbreviation')->unique();
            $table->string('name');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('regions', function(Blueprint $table)
        {
            $table->foreign('parent_id')->references('id')->on('regions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('regions');
    }

}
