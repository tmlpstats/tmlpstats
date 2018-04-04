<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeOrderDefaultNullOnHelpVideoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `help_videos` MODIFY `order` int(10) unsigned DEFAULT 0;");
        // Schema::table('help_videos', function (Blueprint $table) {
        //     $table->integer('order')->unsigned()->nullable()->default(0)->change();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `help_videos` MODIFY `order` int(10) unsigned NOT NULL;");
        // Schema::table('help_videos', function (Blueprint $table) {
        //     $table->integer('order')->unsigned()->change();
        // });
    }
}
