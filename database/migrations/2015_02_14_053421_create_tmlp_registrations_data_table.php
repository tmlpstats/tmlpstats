<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTmlpRegistrationsDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('tmlp_registrations_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->date('reporting_date');
			$table->integer('tmlp_registration_id')->unsigned();
			$table->string('offset');
			$table->string('bef')->nullable();
			$table->string('dur')->nullable();
			$table->string('aft')->nullable();
			$table->string('weekend_reg')->nullable();
			$table->string('app_out')->nullable();
			$table->date('app_out_date')->nullable();
			$table->string('app_in')->nullable();
			$table->date('app_in_date')->nullable();
			$table->string('appr')->nullable();
			$table->date('appr_date')->nullable();
			$table->string('wd')->nullable();
			$table->date('wd_date')->nullable();
			$table->string('committed_team_member_name')->nullable();
			$table->integer('committed_team_member_id')->unsigned()->nullable();
			$table->string('comment')->nullable();
			$table->string('incoming_weekend')->nullable();
			$table->string('reason_withdraw')->nullable();
			$table->string('travel')->nullable();
			$table->string('room')->nullable();
			$table->integer('center_id')->unsigned();
			$table->integer('quarter_id')->unsigned();
			$table->integer('stats_report_id')->unsigned()->nullable();
			$table->timestamps();
		});

		Schema::table('tmlp_registrations_data', function(Blueprint $table)
		{
			$table->foreign('center_id')->references('id')->on('centers');
			$table->foreign('quarter_id')->references('id')->on('quarters');
			$table->foreign('tmlp_registration_id')->references('id')->on('tmlp_registrations');
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
		Schema::drop('tmlp_registrations_data');
	}

}
