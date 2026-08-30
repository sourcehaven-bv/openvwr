<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use App\Config\Feature;
use Tests\TestCase;

use function config;
use function expect;
use function it;
use function uses;

uses(TestCase::class);

it('enables publishing by default so existing deployments are unaffected', function (): void {
    expect(Feature::publishingEnabled())->toBeTrue();
});

it('reports the configured publishing flag', function (bool $enabled): void {
    config()->set('features.publishing', $enabled);

    expect(Feature::publishingEnabled())->toBe($enabled);
})->with([
    'on' => true,
    'off' => false,
]);

it('enables wpg by default so existing deployments are unaffected', function (): void {
    expect(Feature::wpgEnabled())->toBeTrue();
});

it('reports the configured wpg flag', function (bool $enabled): void {
    config()->set('features.wpg', $enabled);

    expect(Feature::wpgEnabled())->toBe($enabled);
})->with([
    'on' => true,
    'off' => false,
]);
