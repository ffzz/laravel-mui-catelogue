<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CacheContentStructures extends Command
{
    /**
     * Command name
     *
     * @var string
     */
    protected $signature = 'data:cache-structures';

    /**
     * Command description
     *
     * @var string
     */
    protected $description = 'Cache data structures for improved performance';

    /**
     * Execute the command
     */
    public function handle()
    {
        $this->info('Caching data structures...');

        // Execute Laravel Data cache command
        $this->call('data:cache-structures');

        $this->info('Data structures cached successfully!');

        return Command::SUCCESS;
    }
}
