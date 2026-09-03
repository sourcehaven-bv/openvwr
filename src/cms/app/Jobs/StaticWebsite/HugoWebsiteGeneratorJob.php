<?php

declare(strict_types=1);

namespace App\Jobs\StaticWebsite;

use App\Config\Feature;
use App\Repositories\AdminLogRepository;
use App\Services\StaticWebsite\StaticWebsiteGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Psr\Log\LoggerInterface;

use function sprintf;

class HugoWebsiteGeneratorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(
        AdminLogRepository $adminLogRepository,
        Application $application,
        LoggerInterface $logger,
    ): void {
        if (!Feature::publishingEnabled()) {
            $logger->info('Hugo website generation skipped: publishing feature is disabled');

            return;
        }

        /** @var StaticWebsiteGenerator $websiteGenerator */
        $websiteGenerator = $application->get(StaticWebsiteGenerator::class);

        $adminLogRepository->timedLog(
            static function () use ($websiteGenerator): void {
                $websiteGenerator->generate();
            },
            sprintf('Processed job: "%s"', self::class),
        );
    }
}
