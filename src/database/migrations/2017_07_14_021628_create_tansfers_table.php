<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTansfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('center_id')->unsigned();
            $table->date('reporting_date');
            $table->string('subject_type', 50);
            $table->integer('subject_id')->unsigned();
            $table->string('transfer_type', 50);
            $table->integer('from_id')->unsigned();
            $table->integer('to_id')->unsigned();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::table('transfers', function (Blueprint $table) {
            $table->foreign('center_id')->references('id')->on('centers');
            $table->index(['center_id', 'subject_type', 'subject_id', 'transfer_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('transfers');
    }
}
