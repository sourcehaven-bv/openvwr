<?php

declare(strict_types=1);

namespace App\Listeners\StaticWebsite;

use App\Config\Feature;
use App\Jobs\StaticWebsite\ContentGeneratorJob;
use App\Jobs\StaticWebsite\HugoWebsiteGeneratorJob;
use App\Jobs\StaticWebsite\StaticWebsiteCheckJob;
use Illuminate\Support\Facades\Bus;
use Psr\Log\LoggerInterface;

readonly class BuildHandler
{
    public function __construct(
        private LoggerInterface $logger,
    ) {
    }

    public function handle(): void
    {
        if (!Feature::publishingEnabled()) {
            $this->logger->info('Static website build skipped: publishing feature is disabled');

            return;
        }

        $this->logger->debug('StaticWebsite build handler triggered');

        $jobs = [
            new ContentGeneratorJob(),
            new HugoWebsiteGeneratorJob(),
            new StaticWebsiteCheckJob(),
        ];

        Bus::chain($jobs)->dispatch();
    }
}
