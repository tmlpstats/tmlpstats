<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockedToStatsReportsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('stats_reports', function(Blueprint $table)
		{
			$table->integer('locked')->tinyInteger()->default(0);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('stats_reports', function(Blueprint $table)
		{
			$table->dropColumn('locked');
		});
	}

}
