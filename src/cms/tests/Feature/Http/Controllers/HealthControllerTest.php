<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Services\DatabaseHealthService;
use App\Services\Virusscanner\Virusscanner;

use function it;
use function time;

it('returns a valid health response', function (): void {
    $this->get('/health')->assertOk();
});

it('returns 200 on /up when healthy', function (): void {
    $this->mock(DatabaseHealthService::class)
        ->shouldReceive('isHealthy')
        ->once()
        ->andReturn(true);
    $this->mock(Virusscanner::class)
        ->shouldReceive('isHealthy')
        ->once()
        ->andReturn(true);

    $this->get('/up')->assertOk()->assertContent('');
});

it('returns 503 on /up when unhealthy', function (bool $databaseHealthy, bool $virusscannerHealthy): void {
    $this->mock(DatabaseHealthService::class)
        ->shouldReceive('isHealthy')
        ->once()
        ->andReturn($databaseHealthy);
    $this->mock(Virusscanner::class)
        ->shouldReceive('isHealthy')
        ->once()
        ->andReturn($virusscannerHealthy);

    $this->get('/up')->assertServiceUnavailable();
})->with([
    'database unhealthy' => [false, true],
    'virusscanner unhealthy' => [true, false],
    'all unhealthy' => [false, false],
]);

it('returns health status in OhDear format', function (bool $databaseHealthy, bool $virusscannerHealthy): void {
    $this->mock(DatabaseHealthService::class)
        ->shouldReceive('isHealthy')
        ->once()
        ->andReturn($databaseHealthy);
    $this->mock(Virusscanner::class)
        ->shouldReceive('isHealthy')
        ->once()
        ->andReturn($virusscannerHealthy);

    $before = time();
    $response = $this->get('/health');
    $after = time();

    $response->assertOk();
    $response->assertJsonStructure([
        'finishedAt',
        'checkResults' => [
            '*' => ['name', 'label', 'status', 'notificationMessage', 'shortSummary', 'meta'],
        ],
    ]);

    $json = $response->json();

    expect($json['finishedAt'])->toBeGreaterThanOrEqual($before);
    expect($json['finishedAt'])->toBeLessThanOrEqual($after);

    $database = collect($json['checkResults'])->firstWhere('name', 'Database');
    $virusscanner = collect($json['checkResults'])->firstWhere('name', 'Virusscanner');

    expect($database['status'])->toBe($databaseHealthy ? 'ok' : 'failed');
    expect($virusscanner['status'])->toBe($virusscannerHealthy ? 'ok' : 'failed');
})->with([
    'all healthy' => [true, true],
    'database unhealthy' => [false, true],
    'virusscanner unhealthy' => [true, false],
    'all unhealthy' => [false, false],
]);
