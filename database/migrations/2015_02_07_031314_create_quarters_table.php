<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuartersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('quarters', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('location', 128);
			$table->string('distinction');
			$table->date('start_weekend_date')->unique();
			$table->date('end_weekend_date');
			$table->date('classroom1_date');
			$table->date('classroom2_date');
			$table->date('classroom3_date');
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
		Schema::drop('quarters');
	}

}
