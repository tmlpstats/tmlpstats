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
            $table->date('reporting_date');
            $table->integer('course_id')->unsigned();
            $table->string('offset');
            $table->string('quarter_start_ter')->nullable();
            $table->string('quarter_start_standard_starts')->nullable();
            $table->string('quarter_start_xfer')->nullable();
            $table->string('current_ter')->nullable();
            $table->string('current_standard_starts')->nullable();
            $table->string('current_xfer')->nullable();
            $table->string('completed_standard_starts')->nullable();
            $table->string('potentials')->nullable();
            $table->string('registrations')->nullable();
            $table->integer('center_id')->unsigned();
            $table->integer('quarter_id')->unsigned();
            $table->integer('stats_report_id')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('courses_data', function(Blueprint $table)
        {
            $table->foreign('center_id')->references('id')->on('centers');
            $table->foreign('quarter_id')->references('id')->on('quarters');
            $table->foreign('course_id')->references('id')->on('courses');
            // Not adding a foreign key for stats_reports because there's a circular reference. Adding stats_report
            // to make it easier to delete all data added by a stats_report if it fails validation
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
