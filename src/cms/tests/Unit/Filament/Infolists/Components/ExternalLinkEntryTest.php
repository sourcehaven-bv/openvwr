<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Infolists\Components;

use App\Filament\Infolists\Components\ExternalLinkEntry;
use Tests\TestCase;

use function expect;
use function it;
use function uses;

uses(TestCase::class);

it('exposes the linkable check and the configured view to the blade', function (): void {
    $entry = ExternalLinkEntry::make('location');

    $isLinkable = $entry->getViewData()['isLinkable'];

    expect($entry->getView())->toBe('filament.infolists.components.entries.external-link-entry')
        ->and($isLinkable('https://12345.afasinsite.nl/dossier?id=43543'))->toBeTrue()
        ->and($isLinkable('  https://12345.afasinsite.nl/dossier  '))->toBeTrue();
});

it('reports non-url locations as not linkable', function (?string $state): void {
    $isLinkable = ExternalLinkEntry::make('location')->getViewData()['isLinkable'];

    expect($isLinkable($state))->toBeFalse();
})->with([
    'null' => null,
    'empty' => '',
    'dms reference' => 'DMS-2024-00184',
    'network path' => '\\\\fileserver\\privacy\\verwerkersovereenkomsten',
]);

it('reports unsafe schemes as not linkable', function (string $state): void {
    $isLinkable = ExternalLinkEntry::make('location')->getViewData()['isLinkable'];

    expect($isLinkable($state))->toBeFalse();
})->with([
    'javascript' => 'javascript:alert(document.cookie)',
    'data' => 'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
    'file' => 'file:///Volumes/privacy/document.pdf',
]);
