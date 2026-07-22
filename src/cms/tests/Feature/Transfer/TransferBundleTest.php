<?php

declare(strict_types=1);

use App\Transfer\Import\TransferBundle;

it('reads the source organisation name and exported at from the manifest', function (): void {
    $bundle = new TransferBundle([
        'source_organisation' => ['name' => 'Bron BV'],
        'exported_at' => '2026-07-21T10:00:00+00:00',
    ], []);

    expect($bundle->sourceOrganisationName())->toBe('Bron BV')
        ->and($bundle->exportedAt())->toBe('2026-07-21T10:00:00+00:00');
});

it('returns empty strings when the source organisation is missing or malformed', function (): void {
    $missing = new TransferBundle([], []);
    $notAnArray = new TransferBundle(['source_organisation' => 'nope'], []);
    $noName = new TransferBundle(['source_organisation' => ['id' => 'x']], []);
    $nonStringName = new TransferBundle(['source_organisation' => ['name' => 123]], []);

    expect($missing->sourceOrganisationName())->toBe('')
        ->and($notAnArray->sourceOrganisationName())->toBe('')
        ->and($noName->sourceOrganisationName())->toBe('')
        ->and($nonStringName->sourceOrganisationName())->toBe('');
});

it('returns an empty string when exported at is missing or not a string', function (): void {
    $missing = new TransferBundle([], []);
    $nonString = new TransferBundle(['exported_at' => 42], []);

    expect($missing->exportedAt())->toBe('')
        ->and($nonString->exportedAt())->toBe('');
});
