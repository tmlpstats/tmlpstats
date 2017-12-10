<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRppToTeamMembersDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_members_data', function (Blueprint $table) {
            $table->integer('rpp_cap')->unsigned()->default(0)->after('tdo');
            $table->integer('rpp_cpc')->unsigned()->default(0)->after('rpp_cap');
            $table->integer('rpp_lf')->unsigned()->default(0)->after('rpp_cpc');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('team_members_data', function (Blueprint $table) {
            $table->dropColumn('rpp_cap');
            $table->dropColumn('rpp_cpc');
            $table->dropColumn('rpp_lf');
        });
    }
}
