<?php namespace TmlpStats\Console\Commands;

use DB;
use Schema;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ClearTablesCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Drop all tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // NEVER IN PROD!
        if (env('APP_ENV') !== 'local') {
            return false;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        DB::table('accountability_person')->truncate();
        DB::table('center_stats_data')->truncate();
        DB::table('courses')->truncate();
        DB::table('courses_data')->truncate();
        DB::table('global_report_stats_report')->truncate();
        DB::table('global_reports')->truncate();
//        DB::table('stats_reports')->truncate();
        DB::table('team_members')->truncate();
        DB::table('team_members_data')->truncate();
        DB::table('tmlp_games_data')->truncate();
        DB::table('tmlp_registrations')->truncate();
        DB::table('tmlp_registrations_data')->truncate();

        DB::table('people')->where('id', '>', 85)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}
