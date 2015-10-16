<?php

use TmlpStats\Accountability;
use TmlpStats\Person;
use TmlpStats\ProgramTeamMember;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertProgramTeamMembersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $teamMembers = ProgramTeamMember::all();
        foreach ($teamMembers as $member) {

            $person = Person::create([
                'first_name' => $member->firstName,
                'last_name'  => $member->lastName,
                'phone'      => $member->phone,
                'email'      => $member->email,
                'center_id'  => $member->centerId ?: null,
            ]);

            $accountability = null;
            switch ($member->accountability) {
                case 'Statistician':
                    $accountability = Accountability::name('teamStatistician')->first();
                    break;
                case 'Statistician Apprentic':
                    $accountability = Accountability::name('teamStatisticianApprentice')->first();
                    break;
                case 'Team 1 Team Leader':
                    $accountability = Accountability::name('team1TeamLeader')->first();
                    break;
                case 'Team 2 Team Leader':
                    $accountability = Accountability::name('team2TeamLeader')->first();
                    break;
                case 'Program Manager':
                    $accountability = Accountability::name('programManager')->first();
                    break;
                case 'Classroom Leader':
                    $accountability = Accountability::name('classroomLeader')->first();
                    break;
                case 'Team Mailing List':
                    $accountability = Accountability::name('teamMailingList')->first();
                    break;
            }

            if ($accountability) {
                $person->accountabilities()->attach($accountability);
            }
        }
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
