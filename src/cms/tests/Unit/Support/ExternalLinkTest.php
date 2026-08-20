<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\ExternalLink;
use Tests\TestCase;

use function expect;
use function it;
use function uses;

uses(TestCase::class);

it('treats http(s) urls as linkable', function (string $value): void {
    expect(ExternalLink::isLinkable($value))->toBeTrue();
})->with([
    'afas insite dossier item' => 'https://12345.afasinsite.nl/dossier?id=43543',
    'afas insite custom domain' => 'https://insite.example.org/dossier-prs?sbid=43543',
    'plain http' => 'http://example.org/document.pdf',
    'uppercase scheme' => 'HTTPS://12345.afasinsite.nl/dossier',
    'surrounding whitespace' => '  https://12345.afasinsite.nl/dossier  ',
]);

it('does not linkify non-url references', function (?string $value): void {
    expect(ExternalLink::isLinkable($value))->toBeFalse();
})->with([
    'null' => null,
    'empty' => '',
    'blank' => '   ',
    'dms reference' => 'DMS-2024-00184',
    'windows network path' => '\\\\fileserver\\privacy\\verwerkersovereenkomsten',
    'bare host without scheme' => '12345.afasinsite.nl/dossier',
    'sentence mentioning a url' => 'Zie https://12345.afasinsite.nl/dossier voor het origineel',
]);

it('refuses schemes that are unsafe to render as an href', function (string $value): void {
    expect(ExternalLink::isLinkable($value))->toBeFalse();
})->with([
    'javascript' => 'javascript:alert(document.cookie)',
    'javascript uppercase' => 'JavaScript:alert(1)',
    'data' => 'data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==',
    'file' => 'file:///Volumes/privacy/document.pdf',
    'ftp' => 'ftp://example.org/document.pdf',
    'mailto' => 'mailto:privacy@example.org',
]);

it('requires a host so the link cannot resolve back to the cms', function (): void {
    expect(ExternalLink::isLinkable('https:///dossier'))->toBeFalse()
        ->and(ExternalLink::isLinkable('http://'))->toBeFalse();
});
