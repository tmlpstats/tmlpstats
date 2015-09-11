<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses_data', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('stats_report_id')->unsigned()->index();
            $table->integer('course_id')->unsigned();
            $table->integer('quarter_start_ter')->default(0);
            $table->integer('quarter_start_standard_starts')->default(0);
            $table->integer('quarter_start_xfer')->default(0);
            $table->integer('current_ter')->default(0);
            $table->integer('current_standard_starts')->default(0);
            $table->integer('current_xfer')->default(0);
            $table->integer('completed_standard_starts')->nullable();
            $table->integer('potentials')->nullable();
            $table->integer('registrations')->nullable();
            $table->timestamps();
        });

        Schema::table('courses_data', function(Blueprint $table)
        {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->foreign('course_id')->references('id')->on('courses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('courses_data');
    }

}
