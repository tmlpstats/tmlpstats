<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGuestGameToCoursesDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('courses_data', function (Blueprint $table) {
            $table->integer('guests_promised')->nullable()->after('registrations');
            $table->integer('guests_invited')->nullable()->after('guests_promised');
            $table->integer('guests_confirmed')->nullable()->after('guests_invited');
            $table->integer('guests_attended')->nullable()->after('guests_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('courses_data', function (Blueprint $table) {
            $table->dropColumn('guests_promised');
            $table->dropColumn('guests_invited');
            $table->dropColumn('guests_confirmed');
            $table->dropColumn('guests_attended');
        });
    }
}
