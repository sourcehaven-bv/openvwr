<?php

declare(strict_types=1);

use App\Filament\Forms\Components\RecentFirstOptions;
use Tests\TestCase;

uses(TestCase::class);

it('offers the options under a heading that explains the ordering', function (): void {
    $options = ['id-1' => 'Eerste', 'id-2' => 'Tweede'];

    $grouped = RecentFirstOptions::group($options);

    // A single named group: choices.js renders the key as the heading above
    // the options, so the recency order does not read as arbitrary.
    expect($grouped)->toBe([__('general.picker_recent') => $options]);
});

it('keeps the given order', function (): void {
    $options = ['id-3' => 'Zebra', 'id-1' => 'Appel', 'id-2' => 'Mango'];

    $grouped = RecentFirstOptions::group($options);

    // The query orders by updated_at, so that order must survive untouched.
    expect(array_keys($grouped[__('general.picker_recent')]))
        ->toBe(['id-3', 'id-1', 'id-2']);
});

it('drops values that are not plain strings', function (): void {
    $plucked = ['id-1' => 'Naam', 'id-2' => null, 'id-3' => 42];

    expect(RecentFirstOptions::fromPlucked($plucked))->toBe(['id-1' => 'Naam']);
});

it('keeps the ids as strings so they match what the client sends back', function (): void {
    $plucked = ['01a03560-ab71-7161-879c-59d445ac7f30' => 'Systeem'];

    $options = RecentFirstOptions::fromPlucked($plucked);

    expect(array_keys($options))->toBe(['01a03560-ab71-7161-879c-59d445ac7f30']);
});
