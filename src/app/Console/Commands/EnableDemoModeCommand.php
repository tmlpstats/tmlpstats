<?php

namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class EnableDemoModeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo {action}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Put the application in demo mode';

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
    public function handle()
    {
        // NEVER IN PROD!
        if (config('app.env') !== 'local' && config('app.env') !== 'stage') {
            $this->line('Must be in local env... aborting!');
            return false;
        }

        $action = $this->argument('action');
        if ($action !== 'on' && $action !== 'off') {
            $this->line("action must be 'on' or 'off'.");
            return false;
        }

        if ($action === 'on') {
            $this->enable();
            return true;
        }

        if ($action === 'off') {
            $this->disable();
            return true;
        }
    }

    protected function enable()
    {
        if (Storage::exists('demo')) {
            $this->line('Demo mode is already on.');
        } else {
            Storage::put('demo', time());
            $this->line('Demo mode enabled.');
        }

        $this->line('Run the following command to anonymize the data:');
        $this->line('    php artisan db:sanitize');
        return true;
    }

    protected function disable()
    {
        if (!Storage::exists('demo')) {
            $this->line('Demo mode is already off.');
            return;
        }

        Storage::delete('demo');
        $this->line('Demo mode disabled.');
        $this->line('Refresh the db to reset the anonymized data.');

        return true;
    }
}
