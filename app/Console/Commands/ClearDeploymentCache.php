<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ClearDeploymentCache extends Command
{
    protected $signature = 'deployment:clear-cache';

    protected $description = 'Clear Laravel caches after a deployment';

    public function handle(): int
    {
        foreach (['optimize:clear', 'config:clear', 'cache:clear', 'view:clear'] as $command) {
            $this->components->task($command, fn () => Artisan::call($command) === self::SUCCESS);
        }

        $this->newLine();
        $this->info('Deployment caches cleared.');

        return self::SUCCESS;
    }
}
