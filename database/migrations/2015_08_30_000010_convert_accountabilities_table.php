<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertAccountabilitiesTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accountabilities', function(Blueprint $table)
        {
            $table->string('display')->after('context');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accountabilities', function(Blueprint $table)
        {
            $table->dropColumn('display');
        });
    }

}
