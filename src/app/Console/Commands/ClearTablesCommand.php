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
        if (config('app.env') !== 'local') {
            return false;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if (Schema::hasTable('accountability_person')) {
            DB::table('accountability_person')->truncate();
        }
        if (Schema::hasTable('center_stats_data')) {
            DB::table('center_stats_data')->truncate();
        }
        if (Schema::hasTable('courses')) {
            DB::table('courses')->truncate();
        }
        if (Schema::hasTable('courses_data')) {
            DB::table('courses_data')->truncate();
        }
        if (Schema::hasTable('global_report_stats_report')) {
            DB::table('global_report_stats_report')->truncate();
        }
        if (Schema::hasTable('global_reports')) {
            DB::table('global_reports')->truncate();
        }
        if (Schema::hasTable('stats_reports')) {
            DB::table('stats_reports')->truncate();
        }
        if (Schema::hasTable('team_members')) {
            DB::table('team_members')->truncate();
        }
        if (Schema::hasTable('team_members_data')) {
            DB::table('team_members_data')->truncate();
        }
        if (Schema::hasTable('tmlp_games_data')) {
            DB::table('tmlp_games_data')->truncate();
        }
        if (Schema::hasTable('tmlp_registrations')) {
            DB::table('tmlp_registrations')->truncate();
        }
        if (Schema::hasTable('tmlp_registrations_data')) {
            DB::table('tmlp_registrations_data')->truncate();
        }

        if (Schema::hasTable('people')) {
            DB::table('people')->where('id', '>', 85)->delete();
        }

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
