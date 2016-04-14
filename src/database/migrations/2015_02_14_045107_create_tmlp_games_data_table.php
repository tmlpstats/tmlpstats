<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpGamesDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmlp_games_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->integer('quarter_start_registered')->nullable();
            $table->integer('quarter_start_approved')->nullable();
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
        Schema::drop('tmlp_games_data');
    }

}
