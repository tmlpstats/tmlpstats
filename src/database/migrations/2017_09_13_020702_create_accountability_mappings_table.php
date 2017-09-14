<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAccountabilityMappingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::rename('accountability_person', 'accountability_person_old');
        Schema::create('accountability_mappings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('person_id')->unsigned();
            $table->integer('accountability_id')->unsigned();
            $table->integer('center_id')->unsigned();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->timestamps();

            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('accountability_id')->references('id')->on('accountabilities');
            $table->foreign('center_id')->references('id')->on('centers');

            $table->unique(['person_id', 'starts_at', 'accountability_id'], 'idx_ap_person_starts');
            $table->index(['starts_at']);
            $table->index(['ends_at']);
            $table->index(['center_id', 'accountability_id', 'starts_at'], 'idx_ap_center_accountabilities');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accountability_mappings');
    }
}
