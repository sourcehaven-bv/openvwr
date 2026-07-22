<?php

declare(strict_types=1);

use App\Filament\Actions\TransferExportBulkAction;

it('normalises malformed related payloads', function (): void {
    $result = TransferExportBulkAction::selectedRelated([
        'processors' => ['uuid-1', '', 'uuid-2'], // empty string dropped
        'systems' => ['uuid-3'],
        7 => ['ignored'], // non-string relation key skipped
        'documents' => 'not-an-array', // non-array value skipped
        'tags' => [42, 'uuid-4'], // non-string id dropped
    ]);

    expect($result)->toBe([
        'processors' => ['uuid-1', 'uuid-2'],
        'systems' => ['uuid-3'],
        'tags' => ['uuid-4'],
    ]);
});

it('returns an empty array for a non-array related payload', function (): void {
    expect(TransferExportBulkAction::selectedRelated(null))->toBe([])
        ->and(TransferExportBulkAction::selectedRelated('nope'))->toBe([]);
});
