<?php

use TmlpStats\WithdrawCode;
use TmlpStats\TeamMemberData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTeamMembersDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('team_members_data_tmp', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('team_member_id')->unsigned()->index();
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
            $table->integer('stats_report_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->foreign('team_member_id')->references('id')->on('team_members');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
            $table->foreign('accountability_id')->references('id')->on('accountabilities');
        });

        $total = 0;
        $dropped = 0;
        $teamMemberData = TeamMemberData::all();
        foreach ($teamMemberData as $data) {
            $total++;
            // Drop orphan data
            if (!$data->statsReport || !$data->teamMember) {
                $dropped++;
                continue;
            }

            $withdrawCodeId = null;
            if ($data->wd && (is_numeric($data->wd[0]) || $data->wd[0] === 'R')) {
                $code = substr($data->wd, 2);
                $withdrawCode = WithdrawCode::code($code)->first();
                $withdrawCodeId = $withdrawCode ? $withdrawCode->id : null;
            }

            if ($data->wbo) {
                $withdrawCode = WithdrawCode::code('WB')->first();
                $withdrawCodeId = $withdrawCode ? $withdrawCode->id : null;
            }

            DB::table('team_members_data_tmp')->insert([
                'stats_report_id'  => $data->statsReportId,
                'team_member_id'   => $data->teamMemberId,
                'at_weekend'       => !($data->xferIn),
                'xfer_out'         => (bool)$data->xferOut,
                'xfer_in'          => (bool)$data->xferIn,
                'ctw'              => (bool)$data->ctw,
                'rereg'            => (bool)$data->rereg,
                'excep'            => (bool)$data->excep,
                'travel'           => (bool)$data->travel,
                'room'             => (bool)$data->room,
                'comment'          => $data->comment,
                'gitw'             => $data->gitw === 'E',
                'tdo'              => $data->tdo + $data->additionalTdo,
                'withdraw_code_id' => $withdrawCodeId,
            ]);
        }
        echo "Removing {$dropped}/{$total} entries from TeamMemberData\n";

        Schema::table('team_members_data', function (Blueprint $table) {
            $table->drop();
        });
        Schema::table('team_members_data_tmp', function (Blueprint $table) {
            $table->rename('team_members_data');
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
