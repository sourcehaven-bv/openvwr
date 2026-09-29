<?php

declare(strict_types=1);

use App\Jobs\StaticWebsite\HugoWebsiteGeneratorJob;
use App\Repositories\AdminLogRepository;
use App\Services\StaticWebsite\HugoStaticWebsiteGenerator;
use App\Services\StaticWebsite\StaticWebsiteGenerator;
use Psr\Log\LoggerInterface;
use Tests\Helpers\ConfigTestHelper;

it('can run the job', function (): void {
    $adminLogRepository = $this->app->get(AdminLogRepository::class);

    $hugoWebsiteGenerator = $this->mock(HugoStaticWebsiteGenerator::class)
        ->shouldReceive('generate')
        ->once()
        ->getMock();
    $this->app->instance(StaticWebsiteGenerator::class, $hugoWebsiteGenerator);

    $hugoWebsiteGeneratorJob = new HugoWebsiteGeneratorJob();
    $hugoWebsiteGeneratorJob->handle($adminLogRepository, $this->app, $this->app->get(LoggerInterface::class));
});

it('does not resolve the generator when publishing is disabled', function (): void {
    ConfigTestHelper::set('features.publishing', false);
    $adminLogRepository = $this->app->get(AdminLogRepository::class);
    $this->app->bind(StaticWebsiteGenerator::class, static function (): never {
        throw new RuntimeException('Static website generator should not be resolved');
    });

    $logger = $this->mock(LoggerInterface::class)
        ->shouldReceive('info')
        ->once()
        ->with('Hugo website generation skipped: publishing feature is disabled')
        ->getMock();

    $hugoWebsiteGeneratorJob = new HugoWebsiteGeneratorJob();
    $hugoWebsiteGeneratorJob->handle($adminLogRepository, $this->app, $logger);
});
