<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubmissionDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submission_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('center_id')->unsigned();
            $table->date('reporting_date');
            $table->string('stored_type', 50);
            $table->string('stored_id', 50);
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->json('data');
            $table->unique(['center_id', 'reporting_date', 'stored_type', 'stored_id'], 'idx_faux_primary');
        });

        Schema::create('submission_data_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('center_id')->unsigned();
            $table->date('reporting_date');
            $table->string('stored_type', 50);
            $table->string('stored_id', 50);
            $table->integer('user_id')->unsigned();
            $table->timestamps();
            $table->json('data');
            $table->index(['center_id', 'reporting_date', 'stored_type', 'stored_id'], 'idx_center_date_type_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submission_data');
    }
}
