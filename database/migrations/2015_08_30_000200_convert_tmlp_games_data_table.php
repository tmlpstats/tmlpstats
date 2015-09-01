<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTmlpGamesDataTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tmlp_games_data', function(Blueprint $table)
        {
            $table->integer('stats_report_id')->unsigned()->index();
            $table->string('type');

            $tmlpGameData = \TmlpStats\TmlpGameData::all();
            foreach ($tmlpGameData as $data) {
                $data->type = $data->tmlpGame->type;
            }

            $table->dropForeign('center_id');
            $table->dropForeign('quarter_id');
            $table->dropForeign('tmlp_game_id');

            $table->dropColumn('reporting_date');
            $table->dropColumn('tmlp_game_id');
            $table->dropColumn('offset');
            $table->dropColumn('center_id');
            $table->dropColumn('quarter_id');
        });

        Schema::table('tmlp_games_data', function(Blueprint $table)
        {
            $table->foreign('stats_report_id')->references('id')->on('stats_reports');
        });
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
