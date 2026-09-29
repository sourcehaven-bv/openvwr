<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Config\Feature;
use App\Events\StaticWebsite\BuildEvent;
use Illuminate\Console\Command;

class StaticWebsiteRefresh extends Command
{
    protected $signature = 'static-website:refresh';
    protected $description = 'Regenerate & publish the static-website';

    public function handle(): int
    {
        if (!Feature::publishingEnabled()) {
            $this->output->info('Static website refresh skipped: publishing feature is disabled');

            return self::SUCCESS;
        }

        BuildEvent::dispatch();

        $this->output->success('Static website refresh jobs dispatched, see worker logs for details');

        return self::SUCCESS;
    }
}
