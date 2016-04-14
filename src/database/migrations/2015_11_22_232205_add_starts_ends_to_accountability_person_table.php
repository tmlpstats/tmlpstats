<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Carbon\Carbon;
use TmlpStats\Accountability;

class AddStartsEndsToAccountabilityPersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Schema::table('accountability_person', function (Blueprint $table) {
        //    $table->timestamp('starts_at')->default(DB::raw('CURRENT_TIMESTAMP'))->after('accountability_id');
        //    $table->timestamp('ends_at')->nullable()->default(null)->after('starts_at');
        //});
        //
        //DB::table('accountability_person')
        //    ->update(['starts_at' => Carbon::now()->subWeeks(13)]);
        //
        //DB::table('accountability_person')
        //    ->where('active', false)
        //    ->update(['ends_at' => Carbon::now()->subWeeks(13)]);
        //
        //$accountabilities = Accountability::all();
        //foreach ($accountabilities as $accountability) {
        //    switch ($accountability->name) {
        //        case 'teamStatistician':
        //            $accountability->name = 'statistician';
        //            $accountability->save();
        //            break;
        //        case 'teamStatisticianApprentice':
        //            $accountability->name = 'statisticianApprentice';
        //            $accountability->save();
        //            break;
        //        case 'team1TeamLeader':
        //            $accountability->name = 't1tl';
        //            $accountability->save();
        //            break;
        //        case 'team2TeamLeader':
        //            $accountability->name = 't2tl';
        //            $accountability->save();
        //            break;
        //        default:
        //            continue;
        //    }
        //}
        //
        //Schema::table('accountability_person', function (Blueprint $table) {
        //    $table->dropColumn('active');
        //});
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::table('accountability_person', function (Blueprint $table) {
        //    $table->boolean('active')->default(true)->after('accountability_id');
        //
        //    DB::table('accountability_person')
        //        ->where('ends_at', '<', Carbon::now())
        //        ->update(['active' => false]);
        //
        //    $table->dropColumn('starts_at');
        //    $table->dropColumn('ends_at');
        //});
    }
}
