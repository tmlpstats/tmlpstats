<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGlobalReportsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_reports', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date('reporting_date');
            $table->integer('quarter_id')->unsigned();
            $table->integer('locked')->tinyInteger()->default(0);
            $table->integer('user_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('global_reports');
    }

}
