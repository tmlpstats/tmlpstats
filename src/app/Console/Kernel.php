<?php
namespace TmlpStats\Console;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use TmlpStats\Quarter;
use TmlpStats\Region;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ClearTablesCommand::class,
        Commands\EnableDemoModeCommand::class,
        Commands\FlushCacheTagCommand::class,
        Commands\FlushReportsCacheCommand::class,
        Commands\FlushTablesCommand::class,
        Commands\Inspire::class,
        Commands\MergeDuplicatePeople::class,
        Commands\MoveStashesToCenter::class,
        Commands\ReportsCodegen::class,
        Commands\SanitizeDb::class,
        Commands\SendTestEmailCommand::class,
        Commands\UpdatePromise::class,
        Commands\SendInterestResponseEmails::class,
        Commands\CreateQuarter::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('create:quarter')
                 ->dailyAt('15:00')
                 ->when(function () {
                     $regions = Region::isGlobal()->get();

                     $earliest_cl3 = Carbon::maxValue();
                     foreach ($regions as $region) {
                         $qrt = Quarter::current($region)->first();
                         $cl3_date = $qrt->getClassroom3Date($region->centers[0]);
                         $earliest_cl3 = $cl3_date < $earliest_cl3 && !$qrt->next_quarter_created ? $cl3_date : $earliest_cl3;
                     }

                     $dt = Carbon::now();

                     return $earliest_cl3->diffInDays($dt) <= 14;

                 })
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/create_quarter.log'));
    }

}
