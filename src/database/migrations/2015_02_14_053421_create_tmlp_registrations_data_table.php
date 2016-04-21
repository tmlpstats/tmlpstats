<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpRegistrationsDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmlp_registrations_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tmlp_registration_id')->unsigned();
            $table->date('reg_date')->nullable();
            $table->date('app_out_date')->nullable();
            $table->date('app_in_date')->nullable();
            $table->date('appr_date')->nullable();
            $table->date('wd_date')->nullable();
            $table->integer('committed_team_member_id')->unsigned()->nullable();
            $table->integer('incoming_quarter_id')->unsigned()->nullable();
            $table->string('comment')->nullable();
            $table->boolean('travel')->default(0);
            $table->boolean('room')->default(0);
            $table->timestamps();
            
            $table->foreign('committed_team_member_id')->references('id')->on('team_members');
            $table->foreign('tmlp_registration_id')->references('id')->on('tmlp_registrations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tmlp_registrations_data');
    }

}
