<?php

use DB;
use TmlpStats\TeamMemberData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeamMembersDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members_data_tmp', function (Blueprint $table) {
            $table->integer('stats_report_id')->unsigned()->index();
            $table->boolean('at_weekend')->default(true);
            $table->boolean('xfer_out')->default(false);
            $table->boolean('xfer_in')->default(false);
            $table->boolean('ctw')->default(false);
            $table->integer('withdraw_code_id')->unsigned()->nullable();
            $table->boolean('rereg')->default(false);
            $table->boolean('excep')->default(false);
            $table->boolean('travel')->default(0);
            $table->boolean('room')->default(0);
            $table->string('comment')->nullable();
            $table->integer('accountability_id')->unsigned()->nullable();
            $table->boolean('gitw')->default(0);
            $table->integer('tdo')->default(0);
            $table->timestamps();

            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->foreign('team_member_id')->references('id')->on('team_members');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
            $table->foreign('accountability_id')->references('id')->on('accountabilities');
        });

        $teamMemberData = TeamMemberData::all();
        foreach ($teamMemberData as $data) {
            $newData = DB::table('team_member_data_tmp')->insert([
                'stats_report_id' => $data->statsReport->id,
                'team_member_id'  => $data->teamMember->id,
                'at_weekend'      => !($data->xferIn),
                'xfer_out'        => $data->xferOut,
                'xfer_in'         => $data->xferIn,
                'ctw'             => $data->ctw,
                'rereg'           => $data->rereg,
                'excep'           => $data->excep,
                'travel'          => $data->travel,
                'room'            => $data->room,
                'comment'         => $data->comment,
                'gitw'            => $data->gitw,
                'tdo'             => $data->tdo + $data->additionalTdo,
            ]);
            // TODO: convert WD code to a withdraw_code
//            'withdraw_code_id' => $data->,
            // TODO: skip?
//            'accountability_id' => $data->,
        }

        Schema::table('team_members_data', function (Blueprint $table) {
            $table->drop();
        });
        Schema::table('team_members_data_tmp', function (Blueprint $table) {
            $table->rename('team_members_data_tmp', 'team_members_data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        // from backup
    }

}
