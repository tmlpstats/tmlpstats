<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpGamesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tmlp_games', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('type');
			$table->integer('center_id')->unsigned();
			$table->integer('stats_report_id')->unsigned()->nullable();
			$table->timestamps();
		});

		Schema::table('tmlp_games', function(Blueprint $table)
		{
			$table->foreign('center_id')->references('id')->on('centers');
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
		Schema::drop('tmlp_games');
	}

}
