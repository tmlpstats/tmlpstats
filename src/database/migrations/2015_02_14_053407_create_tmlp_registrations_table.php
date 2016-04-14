<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpRegistrationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmlp_registrations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_year');
            $table->date('reg_date')->nullable();
            $table->boolean('is_reviewer')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tmlp_registrations');
    }

}
