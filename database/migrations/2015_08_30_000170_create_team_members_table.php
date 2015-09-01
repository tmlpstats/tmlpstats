<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('person_id')->unsigned()->index();
            $table->integer('team_year');
            $table->integer('incoming_quarter_id')->unsigned();
            $table->boolean('is_reviewer')->default(false);
            $table->timestamps();
        });

        Schema::table('team_members', function(Blueprint $table)
        {
            $table->foreign('person_id')->references('id')->on('users');
            $table->foreign('incoming_quarter_id')->references('id')->on('quarters');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('team_members');
    }

}
