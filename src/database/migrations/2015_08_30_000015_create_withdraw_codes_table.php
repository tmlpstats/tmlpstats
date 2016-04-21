<?php

use TmlpStats\WithdrawCode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWithdrawCodesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code')->unique();
            $table->string('display');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('team_members_data', function (Blueprint $table) {
            $table->integer('withdraw_code_id')->unsigned()->nullable()->after('ctw');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
        });

        Schema::table('tmlp_registrations_data', function (Blueprint $table) {
            $table->integer('withdraw_code_id')->unsigned()->nullable()->after('wd_date');
            $table->foreign('withdraw_code_id')->references('id')->on('withdraw_codes');
        });

        $codes = [
            ['AP', 'Chose another program'],
            ['NW', 'Doesn\'t want the training'],
            ['FIN', 'Financial'],
            ['FW', 'Moved to a future weekend'],
            ['MOA', 'Moved out of area'],
            ['NA', 'Not approved'],
            ['OOC', 'Out of communication'],
            ['T', 'Time conversation'],
            ['RE', 'Registration error'],
            ['WB', 'Well-being'],
        ];

        foreach ($codes as $code) {
            WithdrawCode::create([
                'code'    => $code[0],
                'display' => $code[1],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('withdraw_codes');
    }

}
