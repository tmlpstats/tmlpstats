<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use TmlpStats\ReportToken;

class AddRegionToReporttokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('report_tokens', function (Blueprint $table) {
            $table->string('owner_type')->nullable()->default(null)->after('center_id');
            $table->dropForeign('report_tokens_center_id_foreign');
        });

        $tokens = ReportToken::whereNotNull('center_id')->get();
        foreach ($tokens as $token) {
            $token->ownerType = 'TmlpStats\Center';
            $token->save();
        }

        Schema::table('report_tokens', function (Blueprint $table) {
            $table->renameColumn('center_id', 'owner_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('report_tokens', function (Blueprint $table) {
            $table->renameColumn('center_id', 'owner_id');
            $table->dropColumn('owner_type');
            $table->foreign('center_id')->references('id')->on('center');
        });
    }
}
