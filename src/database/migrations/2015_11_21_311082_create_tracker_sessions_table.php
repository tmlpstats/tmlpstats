<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrackerSessionsTable extends Migration {

	/**
	 * Table related to this migration.
	 *
	 * @var string
	 */

	private $table = 'tracker_sessions';

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        // Do not run this migration in testing env
        if (!env('TRACKER_ENABLED')) {
            return;
        }

        Schema::connection('tracker')->create($this->table, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('uuid')->unique()->index();
            $table->bigInteger('user_id')->unsigned()->nullable()->index();
            $table->bigInteger('device_id')->unsigned()->nullable()->index();
            $table->bigInteger('agent_id')->unsigned()->nullable()->index();
            $table->string('client_ip')->index();
            $table->bigInteger('referer_id')->unsigned()->nullable()->index();
            $table->bigInteger('cookie_id')->unsigned()->nullable()->index();
            $table->bigInteger('geoip_id')->unsigned()->nullable()->index();
            $table->boolean('is_robot');

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
        // Do not run this migration in testing env
        if (env('APP_ENV') == 'testing') {
            return;
        }

        Schema::drop($this->table);
	}

}
