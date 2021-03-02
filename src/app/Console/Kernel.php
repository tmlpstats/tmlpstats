<?php
namespace TmlpStats\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')
                 ->hourly();
    }

}
