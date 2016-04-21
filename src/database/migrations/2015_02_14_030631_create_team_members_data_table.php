<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_member_id')->unsigned()->index();
            $table->boolean('at_weekend')->default(true);
            $table->boolean('xfer_out')->default(false);
            $table->boolean('xfer_in')->default(false);
            $table->boolean('ctw')->default(false);
            $table->boolean('rereg')->default(false);
            $table->boolean('excep')->default(false);
            $table->boolean('travel')->default(0);
            $table->boolean('room')->default(0);
            $table->string('comment')->nullable();
            $table->boolean('gitw')->default(0);
            $table->integer('tdo')->default(0);
            $table->timestamps();

            $table->foreign('team_member_id')->references('id')->on('team_members');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('team_members_data');
    }

}
