<?php

use TmlpStats\Accountability;
use TmlpStats\Util;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertAccountabilitiesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('accountabilities', function (Blueprint $table) {
            $table->string('display')->after('context');
        });

        $accountabilities = Accountability::all();
        foreach ($accountabilities as $accountability) {
            $accountability->display = ucwords(Util::toWords($accountability->name));
            $accountability->save();
        }

        Accountability::create([
            'name' => 'teamMailingList',
            'context' => 'team',
            'display' => 'Team Mailing List',
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accountabilities', function (Blueprint $table) {
            $table->dropColumn('display');
        });
    }

}
