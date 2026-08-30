<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Infolists\Components;

use App\Filament\Infolists\Components\SnapshotUrlEntry;
use App\Models\Snapshot;
use Closure;
use Illuminate\Support\Facades\Config;
use ReflectionProperty;
use Tests\TestCase;

use function expect;
use function it;
use function uses;

uses(TestCase::class);

/**
 * Filament resolves the visibility closure through its own evaluation plumbing,
 * which needs a rendered infolist. The closure itself is the interesting part,
 * so it is read off the entry and called directly.
 */
it('hides the snapshot url when publishing is disabled', function (): void {
    Config::set('features.publishing', false);

    $isVisible = (new ReflectionProperty(SnapshotUrlEntry::class, 'isVisible'))
        ->getValue(SnapshotUrlEntry::make());

    expect($isVisible)->toBeInstanceOf(Closure::class)
        ->and($isVisible(new Snapshot()))->toBeFalse();
});

it('falls back to the publication state when publishing is enabled', function (): void {
    Config::set('features.publishing', true);

    $isVisible = (new ReflectionProperty(SnapshotUrlEntry::class, 'isVisible'))
        ->getValue(SnapshotUrlEntry::make());

    expect($isVisible(new Snapshot()))->toBeFalse();
});
