<?php

declare(strict_types=1);

use App\Models\StaticWebsiteCheck;
use App\Services\StaticWebsite\StaticSitemapCheckJsonException;
use App\Services\StaticWebsite\StaticSitemapCheckRequestException;
use App\Services\StaticWebsite\StaticWebsiteCheckService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Psr\Log\LoggerInterface;

it('can check a sitemap and creates a static-website-check', function (): void {
    expect(StaticWebsiteCheck::query()->count())
        ->toBe(0);

    httpFake([
        '*' => Http::response(json_encode([
            'date' => fake()->dateTime()->format('Y-m-d H:i:s'),
            'pages' => [
                [
                    'type' => fake()->word(),
                    'id' => fake()->uuid(),
                    'permalink' => fake()->url(),
                ],
            ],
        ])),
    ]);

    $baseUrl = fake()->url();

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => $baseUrl,
        'proxy' => fake()->url(),
    ]);
    $staticWebsiteCheckService->check();

    expect(StaticWebsiteCheck::query()->count())
        ->toBe(1);

    Http::assertSent(function (Request $request) use ($baseUrl) {
        return $request->url() === sprintf('%s/index.json', $baseUrl);
    });
});

it('will throw retry exception on connection exception', function (): void {
    httpFake([
        '*' => Http::failedConnection(),
    ]);

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
})->throws(StaticSitemapCheckRequestException::class);

it('will throw retry exception on non-ok response', function (): void {
    httpFake([
        '*' => Http::response([], 404),
    ]);

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
})->throws(StaticSitemapCheckRequestException::class);

it('will throw exception on invalid json', function (): void {
    httpFake([
        '*' => Http::response(fake()->word()),
    ]);

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
})->throws(StaticSitemapCheckJsonException::class);

it('will throw exception on valid json but no object', function (): void {
    httpFake([
        '*' => Http::response(json_encode(fake()->word())),
    ]);

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
})->throws(StaticSitemapCheckJsonException::class);

it('will throw exception on valid json but no valid date', function (): void {
    httpFake([
        '*' => Http::response(json_encode(['date' => fake()->randomNumber(1)])),
    ]);

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
})->throws(StaticSitemapCheckJsonException::class);

it('will throw exception on valid json but no valid date format', function (): void {
    httpFake([
        '*' => Http::response(json_encode(['date' => fake()->dateTime()->format('Y-m-d')])),
    ]);

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
})->throws(StaticSitemapCheckJsonException::class);

it('will skip silently (with log entry) if entry with this date already exists', function (): void {
    $date = fake()->dateTime();
    StaticWebsiteCheck::factory()->create(['build_date' => $date]);

    httpFake([
        '*' => Http::response(json_encode(['date' => $date->format('Y-m-d H:i:s')])),
    ]);

    $this->mock(LoggerInterface::class)
        ->shouldReceive('info')
        ->once()
        ->shouldReceive('debug')
        ->once()
        ->with(
            'static-website check, skipping import: entry with build_date exists',
            ['build_date' => $date->format('Y-m-d H:i:s')],
        )
        ->getMock();

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->check();
});

it('will not skip (with log entry) if entry with this date already exists but forced', function (): void {
    expect(StaticWebsiteCheck::query()->count())
        ->toBe(0);

    $date = fake()->dateTime();
    StaticWebsiteCheck::factory()->create(['build_date' => $date]);

    expect(StaticWebsiteCheck::query()->count())
        ->toBe(1);

    httpFake([
        '*' => Http::response(json_encode([
            'date' => $date->format('Y-m-d H:i:s'),
            'pages' => [
                [
                    'type' => fake()->word(),
                    'id' => fake()->uuid(),
                    'permalink' => fake()->url(),
                ],
            ],
        ])),
    ]);

    $this->mock(LoggerInterface::class)
        ->shouldReceive('info')
        ->times(2)
        ->shouldReceive('debug')
        ->once()
        ->getMock();

    $staticWebsiteCheckService = $this->app->make(StaticWebsiteCheckService::class, [
        'baseUrl' => fake()->domainName(),
        'proxy' => null,
    ]);
    $staticWebsiteCheckService->checkForced();

    expect(StaticWebsiteCheck::query()->count())
        ->toBe(2);
});
