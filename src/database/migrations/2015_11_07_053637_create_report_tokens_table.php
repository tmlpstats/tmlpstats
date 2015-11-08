<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 128);
            $table->integer('report_id')->unsigned();
            $table->string('report_type');
            $table->integer('center_id')->unsigned()->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::table('report_tokens', function (Blueprint $table) {
            $table->foreign('center_id')->references('id')->on('centers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('report_tokens');
    }
}
