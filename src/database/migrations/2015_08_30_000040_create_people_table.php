<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePeopleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('people', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->integer('center_id')->unsigned()->nullable();
            $table->string('identifier');
            $table->timestamps();

            $table->foreign('center_id')->references('id')->on('centers');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('person_id')->unsigned()->nullable()->index()->nullable()->after('managed');
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->integer('person_id')->unsigned()->nullable()->index()->after('id');
            $table->foreign('person_id')->references('id')->on('people');
        });

        Schema::table('tmlp_registrations', function (Blueprint $table) {
            $table->integer('person_id')->unsigned()->nullable()->after('id');
            $table->foreign('person_id')->references('id')->on('people');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('people');
    }
}
