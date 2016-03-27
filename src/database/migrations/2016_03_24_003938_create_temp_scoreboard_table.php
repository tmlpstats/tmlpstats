<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTempScoreboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_scoreboard', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('center_id')->unsigned();
            $table->string('routing_key', 150);
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
            $table->integer('value');
            $table->unique(['center_id', 'routing_key']);
        });

        Schema::create('temp_scoreboard_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('center_id')->unsigned();
            $table->integer('quarter_id')->unsigned()->nullable();
            $table->integer('stats_report_id')->unsigned()->nullable();
            $table->string('game', 50)->nullable();
            $table->string('type', 50)->nullable();
            $table->integer('value');
            $table->timestamps();
            $table->index(['center_id', 'quarter_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_scoreboard_log');
        Schema::dropIfExists('temp_scoreboard');
    }
}
