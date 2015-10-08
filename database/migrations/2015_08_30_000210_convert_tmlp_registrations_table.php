<?php

use TmlpStats\Person;
use TmlpStats\TmlpRegistration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTmlpRegistrationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmlp_registrations', function (Blueprint $table) {
            $table->integer('person_id')->unsigned()->after('id');
            $table->integer('team_year')->after('person_id');
        });

        $registrations = TmlpRegistration::all();
        foreach ($registrations as $incoming) {
            $person = Person::create([
                'first_name' => $incoming->firstName,
                'last_name'  => $incoming->lastName,
                'center_id'  => $incoming->centerId ?: null,
            ]);

            $incoming->personId = $person->id;
            $incoming->teamYear = $incoming->incomingTeamYear;

            $incoming->save();
        }

        Schema::table('tmlp_registrations', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people');

            $table->dropIndex('tmlp_registrations_center_id_foreign');

            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
//            $table->dropColumn('reg_date');
            $table->dropColumn('incoming_team_year');
            $table->dropColumn('center_id');
            $table->dropColumn('stats_report_id');
        });
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
