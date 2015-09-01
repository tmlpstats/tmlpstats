<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatsReportsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats_reports', function(Blueprint $table)
        {
            $table->increments('id');
            $table->date('reporting_date')->index();
            $table->integer('center_id')->unsigned();
            $table->integer('quarter_id')->unsigned();
            $table->integer('user_id')->unsigned();
            $table->string('version');
            $table->boolean('validated')->default(false);
            $table->boolean('locked')->default(false);
            $table->timestamp('submitted_at')->nullable()->default(null);
            $table->string('submit_comment', 8096)->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('stats_reports', function(Blueprint $table)
        {
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('quarter_id')->references('id')->on('quarters');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('stats_reports');
    }

}
