<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSystemMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('center_id')->unsigned()->nullable();
            $table->integer('region_id')->unsigned()->nullable();
            $table->integer('author_id')->unsigned();
            $table->boolean('active');
            $table->string('section', 50);
            $table->json('data');
            // Optimize the most common query made: WHERE section=<x> AND active=<true>
            $table->index(['section', 'active'], 'idx_section_active');
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('region_id')->references('id')->on('regions');
            $table->foreign('author_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_messages');
    }
}
