<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Infolists\Components;

use App\Filament\Infolists\Components\ExternalLinkEntry;
use Tests\TestCase;

use function expect;
use function it;
use function uses;

uses(TestCase::class);

it('links an http(s) location and opens it in a new tab', function (): void {
    $entry = ExternalLinkEntry::make('location')
        ->state('https://12345.afasinsite.nl/dossier?id=43543');

    expect($entry->getUrl())->toBe('https://12345.afasinsite.nl/dossier?id=43543')
        ->and($entry->shouldOpenUrlInNewTab())->toBeTrue();
});

it('trims surrounding whitespace from the generated href', function (): void {
    $entry = ExternalLinkEntry::make('location')
        ->state('  https://12345.afasinsite.nl/dossier  ');

    expect($entry->getUrl())->toBe('https://12345.afasinsite.nl/dossier');
});

it('renders a non-url location as plain text', function (?string $state): void {
    $entry = ExternalLinkEntry::make('location')->state($state);

    expect($entry->getUrl())->toBeNull()
        ->and($entry->shouldOpenUrlInNewTab())->toBeFalse();
})->with([
    'empty' => '',
    'dms reference' => 'DMS-2024-00184',
    'network path' => '\\\\fileserver\\privacy\\verwerkersovereenkomsten',
]);

/*
 * A record with an empty location resolves its state through a closure rather
 * than ->state(), because ->state(null) makes Filament fall back to the
 * component container, which does not exist for a standalone component.
 */
it('renders an unset location as plain text', function (): void {
    $entry = ExternalLinkEntry::make('location')
        ->getStateUsing(static fn (): ?string => null);

    expect($entry->getUrl())->toBeNull()
        ->and($entry->shouldOpenUrlInNewTab())->toBeFalse();
});

it('never turns an unsafe scheme into an href', function (string $state): void {
    $entry = ExternalLinkEntry::make('location')->state($state);

    expect($entry->getUrl())->toBeNull()
        ->and($entry->shouldOpenUrlInNewTab())->toBeFalse();
})->with([
    'javascript' => 'javascript:alert(document.cookie)',
    'data' => 'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
    'file' => 'file:///Volumes/privacy/document.pdf',
]);
