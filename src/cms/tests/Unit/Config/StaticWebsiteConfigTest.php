<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use Closure;
use Illuminate\Support\Env;
use Tests\TestCase;

use function beforeEach;
use function config_path;
use function expect;
use function it;
use function putenv;
use function uses;

uses(TestCase::class);

beforeEach(function (): void {
    /**
     * Loads config/static-website.php with the given environment applied, so the
     * env() fallbacks in that file are exercised the way a real deployment does.
     * A value of false means the variable is absent from the environment.
     *
     * @param array<string, string|false> $environment
     *
     * @return array<string, mixed>
     */
    $this->loadStaticWebsiteConfig = static function (array $environment): array {
        $forget = static function (string $name): void {
            Env::getRepository()->clear($name);
            putenv($name);
        };

        $originalValues = [];

        foreach ($environment as $name => $value) {
            $originalValues[$name] = Env::getRepository()->get($name);

            if ($value === false) {
                $forget($name);

                continue;
            }

            Env::getRepository()->set($name, $value);
        }

        try {
            return require config_path('static-website.php');
        } finally {
            foreach ($originalValues as $name => $originalValue) {
                if ($originalValue === null) {
                    $forget($name);

                    continue;
                }

                Env::getRepository()->set($name, $originalValue);
            }
        }
    };
});

it('falls back to the application url when the static website base url is not configured', function (): void {
    /** @var Closure $loadStaticWebsiteConfig */
    $loadStaticWebsiteConfig = $this->loadStaticWebsiteConfig;

    // HugoStaticWebsiteGenerator::__construct() type hints $baseUrl as string, so a
    // null here becomes a TypeError when the container resolves it, not a warning.
    $staticWebsiteConfig = $loadStaticWebsiteConfig([
        'APP_URL' => 'https://tenant.example.test',
        'STATIC_WEBSITE_BASE_URL' => false,
        'STATIC_WEBSITE_CHECK_BASE_URL' => false,
    ]);

    expect($staticWebsiteConfig['base_url'])->toBe('https://tenant.example.test')
        ->and($staticWebsiteConfig['check_base_url'])->toBe('https://tenant.example.test');
});

it('prefers an explicit static website base url over the application url', function (): void {
    /** @var Closure $loadStaticWebsiteConfig */
    $loadStaticWebsiteConfig = $this->loadStaticWebsiteConfig;

    $staticWebsiteConfig = $loadStaticWebsiteConfig([
        'APP_URL' => 'https://tenant.example.test',
        'STATIC_WEBSITE_BASE_URL' => 'https://static.example.test',
        'STATIC_WEBSITE_CHECK_BASE_URL' => false,
    ]);

    expect($staticWebsiteConfig['base_url'])->toBe('https://static.example.test')
        ->and($staticWebsiteConfig['check_base_url'])->toBe('https://static.example.test');
});

it('prefers an explicit check base url over the static website base url', function (): void {
    /** @var Closure $loadStaticWebsiteConfig */
    $loadStaticWebsiteConfig = $this->loadStaticWebsiteConfig;

    $staticWebsiteConfig = $loadStaticWebsiteConfig([
        'APP_URL' => 'https://tenant.example.test',
        'STATIC_WEBSITE_BASE_URL' => 'https://static.example.test',
        'STATIC_WEBSITE_CHECK_BASE_URL' => 'https://check.example.test',
    ]);

    expect($staticWebsiteConfig['base_url'])->toBe('https://static.example.test')
        ->and($staticWebsiteConfig['check_base_url'])->toBe('https://check.example.test');
});
