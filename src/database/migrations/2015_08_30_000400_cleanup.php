<?php

use TmlpStats\Quarter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Cleanup extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //$quarters = Quarter::all();
        //
        //foreach ($quarters as $quarter) {
        //    if ($quarter->deleteMe) {
        //        $quarter->delete();
        //    }
        //}
        //Schema::table('quarters', function (Blueprint $table) {
        //    $table->dropColumn('delete_me');
        //    $table->unique(array('quarter_number', 'year'));
        //});

        //Schema::drop('program_team_members');
        //Schema::drop('tmlp_games');
        //Schema::drop('center_user');
        //Schema::drop('role_user');
        //Schema::drop('center_stats');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }

}
