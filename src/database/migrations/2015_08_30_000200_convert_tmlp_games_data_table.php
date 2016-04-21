<?php

use TmlpStats\TmlpGame;
use TmlpStats\TmlpGameData;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConvertTmlpGamesDataTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('tmlp_games_data', function (Blueprint $table) {
        //    $table->index('stats_report_id');
        //    $table->string('type')->after('id');
        //});
        //
        //$total = 0;
        //$dropped = 0;
        //$tmlpGameData = TmlpGameData::all();
        //foreach ($tmlpGameData as $data) {
        //    $total++;
        //
        //    $game = TmlpGame::find($data->tmlpGameId);
        //    $data->type = $game ? $game->type : null;
        //
        //    if (!$game || !$data->statsReport) {
        //        // Drop orphan data
        //        $dropped++;
        //        $data->delete();
        //        continue;
        //    }
        //    $data->save();
        //}
        //echo "Removing {$dropped}/{$total} entries from TmlpGameData\n";
        //
        //Schema::table('tmlp_games_data', function (Blueprint $table) {
        //    $table->dropIndex('tmlp_games_data_center_id_foreign');
        //    $table->dropIndex('tmlp_games_data_quarter_id_foreign');
        //    $table->dropIndex('tmlp_games_data_tmlp_game_id_foreign');
        //
        //    $table->dropColumn('reporting_date');
        //    $table->dropColumn('tmlp_game_id');
        //    $table->dropColumn('offset');
        //    $table->dropColumn('center_id');
        //    $table->dropColumn('quarter_id');
        //
        //    $table->foreign('stats_report_id')->references('id')->on('stats_reports');
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
