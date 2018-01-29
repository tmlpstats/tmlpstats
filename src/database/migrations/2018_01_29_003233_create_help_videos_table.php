<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHelpVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_videos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 256);
            $table->string('description', 1024);
            $table->string('url', 1024);
            $table->string('access_group', 32);
            $table->boolean('active')->default(true);
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
        Schema::drop('help_videos');
    }
}
