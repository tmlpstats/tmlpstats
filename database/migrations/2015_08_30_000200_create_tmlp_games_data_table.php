<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpGamesDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmlp_games_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('stats_report_id')->unsigned()->index();
            $table->string('type');
            $table->integer('quarter_start_registered')->default(0);
            $table->integer('quarter_start_approved')->default(0);
            $table->timestamps();
        });

        Schema::table('tmlp_games_data', function(Blueprint $table)
        {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
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
