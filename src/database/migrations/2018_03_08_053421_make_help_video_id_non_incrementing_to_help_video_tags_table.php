<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeHelpVideoIdNonIncrementingToHelpVideoTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('help_video_tags', function (Blueprint $table) {
            $table->integer('help_video_id')->unsigned()->change();
            $table->dropPrimary();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('help_video_tags', function (Blueprint $table) {
            $table->increments('help_video_id')->change();
            $table->primary('help_video_id');
        });
    }
}
