<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveAndContextToWithdrawCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdraw_codes', function (Blueprint $table) {
            $table->boolean('active')->default(true)->after('description');
            $table->string('context')->default('all')->after('active');
        });

        // Update special cases
        $updates = [
            'FW' => 'application',
            'NA' => 'application',
            'RE' => 'application',
        ];

        $codes = DB::table('withdraw_codes')->get();
        foreach ($codes as $code) {
            $changes = [];
            if ($code->code === 'FW') {
                $changes['active'] = false;
            }

            if (isset($updates[$code->code])) {
                $changes['context'] = $updates[$code->code];
            }

            if ($changes) {
                DB::table('withdraw_codes')->where('id', $code->id)->update($changes);
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
        Schema::table('withdraw_codes', function (Blueprint $table) {
            $table->dropColumn('active');
            $table->dropColumn('context');
        });
    }
}
