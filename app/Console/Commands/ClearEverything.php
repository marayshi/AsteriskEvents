<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearEverything extends Command
{

    protected $signature = 'clear:all';

    protected $description = 'Clears routes, config, cache, views, compiled, and caches config.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $validCommands = array('config:cache', 'route:clear', 'config:clear', 'cache:clear', 'view:clear', 'clear-compiled');
        foreach ($validCommands as $cmd) {
            $this->call('' . $cmd . '');

        }
    }
}
