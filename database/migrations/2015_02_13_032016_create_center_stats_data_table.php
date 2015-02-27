<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCenterStatsDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('center_stats_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->date('reporting_date');
			$table->string('type'); // promise/actual
			$table->string('offset');
			$table->string('tdo')->nullable();
			$table->string('cap')->nullable();
			$table->string('cpc')->nullable();
			$table->string('t1x')->nullable();
			$table->string('t2x')->nullable();
			$table->string('gitw')->nullable();
			$table->string('lf')->nullable();
			$table->string('rating')->nullable();
			$table->integer('center_id')->unsigned();
			$table->integer('quarter_id')->unsigned();
			// Not adding a foreign key because there's a circular reference. Adding stats_report
			// to make it easier to delete all data added by a stats_report if it fails validation
			$table->integer('stats_report_id')->unsigned()->nullable();
			$table->timestamps();
		});

		Schema::table('center_stats_data', function(Blueprint $table)
		{
			$table->foreign('center_id')->references('id')->on('centers');
			$table->foreign('quarter_id')->references('id')->on('quarters');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('center_stats_data');
	}
}
