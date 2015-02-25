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
			$table->date('reporting_date');
			$table->integer('tmlp_game_id')->unsigned();
			$table->string('offset');
			$table->integer('quarter_start_registered')->nullable();
			$table->integer('quarter_start_approved')->nullable();
			$table->integer('center_id')->unsigned();
			$table->integer('quarter_id')->unsigned();
			$table->integer('stats_report_id')->unsigned()->nullable();
			$table->timestamps();
		});

		Schema::table('tmlp_games_data', function(Blueprint $table)
		{
			$table->foreign('center_id')->references('id')->on('centers');
			$table->foreign('quarter_id')->references('id')->on('quarters');
			$table->foreign('tmlp_game_id')->references('id')->on('tmlp_games');
			// Not adding a foreign key for stats_reports because there's a circular reference. Adding stats_report
			// to make it easier to delete all data added by a stats_report if it fails validation
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
