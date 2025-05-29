<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use File;

class ClearLog extends Command
{
    protected $signature = 'log:clear';
    protected $description = 'Clear Laravel log file';

    public function handle()
    {
        File::put(storage_path('logs/laravel.log'), '');
        $this->info('Laravel log file cleared.');
    }
}
