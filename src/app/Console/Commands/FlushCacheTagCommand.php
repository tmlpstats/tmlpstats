<?php

namespace TmlpStats\Console\Commands;

use Illuminate\Cache\CacheManager;
use Illuminate\Console\Command;

class FlushCacheTagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-tag {tag}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear specified tag from cache';

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
        $tag = $this->argument('tag');

        $this->cache->store()->tags($tag)->flush();

        $this->info("{$tag} cleared from cache!");
    }
}
