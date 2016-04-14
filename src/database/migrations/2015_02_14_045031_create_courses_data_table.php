<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('course_id')->unsigned();
            $table->string('quarter_start_ter')->nullable();
            $table->string('quarter_start_standard_starts')->nullable();
            $table->string('quarter_start_xfer')->nullable();
            $table->string('current_ter')->nullable();
            $table->string('current_standard_starts')->nullable();
            $table->string('current_xfer')->nullable();
            $table->string('completed_standard_starts')->nullable();
            $table->string('potentials')->nullable();
            $table->string('registrations')->nullable();
            $table->integer('guests_promised')->nullable();
            $table->integer('guests_invited')->nullable();
            $table->integer('guests_confirmed')->nullable();
            $table->integer('guests_attended')->nullable();
            $table->timestamps();

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
