<?php

use TmlpStats\TmlpRegistration;
use TmlpStats\TmlpRegistrationData;
use TmlpStats\WithdrawCode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTmlpRegistrationsDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmlp_registrations_data', function (Blueprint $table) {
            $table->index('stats_report_id');

            $table->date('reg_date')->nullable()->after('tmlp_registration_id');
            $table->integer('withdraw_code_id')->unsigned()->nullable()->after('wd_date');
            $table->integer('incoming_quarter_id')->unsigned()->nullable()->after('committed_team_member_id');
            $table->boolean('travel_tmp')->default(0)->after('travel');
            $table->boolean('room_tmp')->default(0)->after('room');
        });

        $total = 0;
        $dropped = 0;
        $tmlpRegistrationData = TmlpRegistrationData::all();
        foreach ($tmlpRegistrationData as $data) {
            $total++;

            $registration = TmlpRegistration::find($data->tmlpRegistrationId);

            if (!$registration || !$data->statsReport) {
                // Drop orphan data
                $dropped++;
                $data->delete();
                continue;
            }

            $withdrawCodeId = null;
            if ($data->wd && (is_numeric($data->wd[0]) || $data->wd[0] === 'R')) {
                $code = substr($data->wd, 2);
                $withdrawCode = WithdrawCode::code($code)->first();
                $withdrawCodeId = $withdrawCode ? $withdrawCode->id : null;
            }

            $data->regDate = $registration->regDate;
            $data->travelTmp = ($data->travel && strtoupper($data->travel) === 'Y');
            $data->roomTmp = ($data->room && strtoupper($data->room) === 'Y');
            $data->withdrawCodeId = $withdrawCodeId ?: null;

            $data->save();
        }
        echo "Removing {$dropped}/{$total} entries from TmlpRegistrationData\n";

        Schema::table('tmlp_registrations_data', function (Blueprint $table) {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
            $table->foreign('committed_team_member_id')->references('id')->on('team_members');

            $table->dropIndex('tmlp_registrations_data_center_id_foreign');
            $table->dropIndex('tmlp_registrations_data_quarter_id_foreign');

            $table->dropColumn('reporting_date');
            $table->dropColumn('offset');
            $table->dropColumn('bef');
            $table->dropColumn('dur');
            $table->dropColumn('aft');
            $table->dropColumn('weekend_reg');
            $table->dropColumn('app_out');
            $table->dropColumn('app_in');
            $table->dropColumn('appr');
            $table->dropColumn('wd');
            $table->dropColumn('committed_team_member_name');
            $table->dropColumn('incoming_weekend');
            $table->dropColumn('reason_withdraw');
            $table->dropColumn('travel');
            $table->dropColumn('room');
            $table->dropColumn('center_id');
            $table->dropColumn('quarter_id');

            $table->renameColumn('travel_tmp', 'travel');
            $table->renameColumn('room_tmp', 'room');
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
