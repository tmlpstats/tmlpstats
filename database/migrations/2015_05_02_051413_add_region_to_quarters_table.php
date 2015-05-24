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
			$table->string('global_region', 64);
			$table->string('local_region', 64);

			$table->dropUnique('quarters_start_weekend_date_unique');
			$table->unique(array('global_region','local_region','start_weekend_date'));
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
			$table->dropUnique(array('global_region','local_region','start_weekend_date'));
			$table->unique('start_weekend_date');

			$table->dropColumn('global_region');
			$table->dropColumn('local_region');
		});
	}

}
