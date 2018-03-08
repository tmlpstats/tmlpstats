<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeOrderToHelpVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('help_videos', function (Blueprint $table) {
            $table->integer('order')->unsigned()->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('help_videos', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}
