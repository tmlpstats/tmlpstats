<?php

use TmlpStats\TeamMember;
use TmlpStats\Person;
use TmlpStats\Quarter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTeamMembersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('team_members', function (Blueprint $table) {
        //    $table->integer('person_id')->unsigned()->index()->after('id');
        //    $table->boolean('is_reviewer')->default(false)->after('completion_quarter_id');
        //    $table->renameColumn('completion_quarter_id', 'incoming_quarter_id');
        //});
        //
        //$teamMembers = TeamMember::all();
        //foreach ($teamMembers as $member) {
        //    $person = Person::create([
        //        'first_name' => $member->firstName,
        //        'last_name'  => $member->lastName,
        //        'center_id'  => $member->centerId ?: null,
        //    ]);
        //
        //    $member->personId = $person->id;
        //
        //    $incomingQuarter = $member->getIncomingQuarter();
        //    $actualIncomingQuarter = null;
        //    if ($incomingQuarter) {
        //        $startYear = $incomingQuarter->year - 1;
        //        $actualIncomingQuarter = Quarter::year($startYear)
        //            ->quarterNumber($incomingQuarter->quarterNumber)
        //            ->first();
        //    }
        //    $member->incomingQuarterId = $actualIncomingQuarter ? $actualIncomingQuarter->id : null;
        //
        //    $member->save();
        //}
        //
        //Schema::table('team_members', function (Blueprint $table) {
        //
        //    $table->foreign('person_id')->references('id')->on('people');
        //
        //    $table->dropIndex('team_members_user_id_foreign');
        //    $table->dropIndex('team_members_center_id_foreign');
        //
        //    $table->dropColumn('first_name');
        //    $table->dropColumn('last_name');
        //    $table->dropColumn('center_id');
        //    $table->dropColumn('stats_report_id');
        //    $table->dropColumn('user_id');
        //    $table->dropColumn('accountability');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // from backup
    }

}
