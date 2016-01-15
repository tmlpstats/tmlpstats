<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invites', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->integer('role_id')->unsigned();
            $table->integer('center_id')->unsigned();
            $table->integer('invited_by_user_id')->unsigned();
            $table->timestamp('email_sent_at')->nullable();
            $table->string('token', 128);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('invites', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('invited_by_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invites');
    }
}
