<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('team_members_data', function(Blueprint $table)
		{
			$table->increments('id');
			$table->date('reporting_date');
			$table->integer('team_member_id')->unsigned();
			$table->string('offset');
			$table->integer('wknd')->nullable();
			$table->integer('xfer_out')->nullable();
			$table->integer('xfer_in')->nullable();
			$table->integer('ctw')->nullable();
			$table->integer('wd')->nullable();
			$table->integer('wbo')->nullable();
			$table->integer('rereg')->nullable();
			$table->integer('excep')->nullable();
			$table->string('reason_withdraw')->nullable();
			$table->string('travel')->nullable();
			$table->string('room')->nullable();
			$table->string('comment')->nullable();
			$table->string('accountability')->nullable(); //duplicated from team_members to allow us to track changes
			$table->string('gitw')->nullable();
			$table->integer('tdo')->nullable();
			$table->string('additional_tdo')->nullable();
			$table->integer('center_id')->unsigned();
			$table->integer('quarter_id')->unsigned();
			$table->integer('stats_report_id')->unsigned()->nullable();
			$table->timestamps();
		});

		Schema::table('team_members_data', function(Blueprint $table)
		{
			$table->foreign('center_id')->references('id')->on('centers');
			$table->foreign('quarter_id')->references('id')->on('quarters');
			$table->foreign('team_member_id')->references('id')->on('team_members');
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
		Schema::drop('team_members_data');
	}

}
