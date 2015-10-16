<?php namespace TmlpStats\Console\Commands;

use DB;
use Schema;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class FlushTablesCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'db:flush';

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

        $tables = [];
        foreach (DB::select('SHOW TABLES') as $k => $v) {
            $tables[] = array_values((array)$v)[0];
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
            $this->info("Dropped {$table}");
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
