<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveAccountabilityIdFromTeamMembersDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('team_members_data', function (Blueprint $table) {
        //    $table->dropForeign('team_members_data_tmp_accountability_id_foreign');
        //    $table->dropColumn('accountability_id');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::table('team_members_data', function (Blueprint $table) {
        //    $table->integer('accountability_id')->unsigned()->nullable();
        //    $table->foreign('accountability_id')->references('id')->on('accountabilities');
        //});
    }
}
