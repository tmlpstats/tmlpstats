<?php

namespace TmlpStats\Console\Commands;

use Illuminate\Cache\CacheManager;
use Illuminate\Console\Command;

class FlushReportsCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-reports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear reports from cache';

    /**
     * The cache manager instance.
     *
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * Create a new command instance.
     *
     */
    public function __construct(CacheManager $cache)
    {
        parent::__construct();
        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->cache->store()->tags('reports')->flush();

        $this->info('Report cache cleared!');
    }
}
