<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCreatedNextQrtToQuartersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quarters', function (Blueprint $table) {
            $table->boolean('next_quarter_created');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quarters', function (Blueprint $table) {
            $table->dropColumn('next_quarter_created');
        });
    }
}
