<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWboToTeamMembersDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('team_members_data', function (Blueprint $table) {
            $table->boolean('wbo')->default(false)->after('withdraw_code_id');
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
            $table->dropColumn('wbo');
        });
    }
}
