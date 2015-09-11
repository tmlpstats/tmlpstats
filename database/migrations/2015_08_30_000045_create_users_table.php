<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('person_id')->unsigned()->index();
            $table->string('username')->unique();
            $table->string('password', 60);
            $table->integer('role_id')->unsigned()->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('require_password_reset')->default(false);
            $table->timestamp('last_login_at');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function(Blueprint $table)
        {
            $table->foreign('person_id')->references('id')->on('persons');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }

}
