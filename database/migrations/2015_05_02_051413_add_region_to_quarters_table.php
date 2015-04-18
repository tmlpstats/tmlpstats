<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegionToQuartersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('quarters', function(Blueprint $table)
		{
			$table->string('global_region');
			$table->string('local_region');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('quarters', function(Blueprint $table)
		{
			$table->dropColumn('global_region');
			$table->dropColumn('local_region');
		});
	}

}
