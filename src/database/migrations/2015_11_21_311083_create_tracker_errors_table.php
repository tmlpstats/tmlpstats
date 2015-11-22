<?php

use Illuminate\Database\Schema\Blueprint; use Illuminate\Database\Migrations\Migration;

class CreateTrackerErrorsTable extends Migration {

	/**
	 * Table related to this migration.
	 *
	 * @var string
	 */

	private $table = 'tracker_errors';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::connection('tracker')->create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('code')->index();
            $table->string('message')->index();

            $table->timestamp('created_at')->index();
            $table->timestamp('updated_at')->index();
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop($this->table);
	}

}
