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
            $table->date('reporting_date')->index();
            $table->integer('quarter_id')->unsigned();
            $table->integer('user_id')->unsigned()->nullable();
            $table->boolean('locked')->default(0);
            $table->timestamps();
        });

        Schema::table('global_reports', function(Blueprint $table)
        {
            $table->foreign('quarter_id')->references('id')->on('quarters');
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
