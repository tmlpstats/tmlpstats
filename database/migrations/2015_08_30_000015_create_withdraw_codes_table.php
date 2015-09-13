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
