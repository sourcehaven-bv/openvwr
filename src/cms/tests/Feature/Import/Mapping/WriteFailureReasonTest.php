<?php

declare(strict_types=1);

use App\Import\Mapping\WriteFailureReason;
use Illuminate\Database\QueryException;

it('names the kind of refusal without repeating the row', function (string $state, string $key): void {
    $pdo = new PDOException('ERROR: something about the failing row');
    $pdo->errorInfo = [$state, 7, 'DETAIL: Failing row contains (personal data)'];

    $reason = WriteFailureReason::describe(new QueryException('pgsql', 'insert into ...', ['Jansen'], $pdo));

    expect($reason)->toBe(__(sprintf('import_mapping.issue.%s', $key)))
        ->and($reason)->not->toContain('Jansen')
        ->and($reason)->not->toContain('personal data');
})->with([
    'too long' => ['22001', 'write_failed_too_long'],
    'out of range' => ['22003', 'write_failed_out_of_range'],
    'wrong type' => ['22P02', 'write_failed_wrong_type'],
    'required' => ['23502', 'write_failed_required'],
    'reference' => ['23503', 'write_failed_reference'],
    'duplicate' => ['23505', 'write_failed_duplicate'],
    'anything else' => ['XX000', 'write_failed'],
]);

it('falls back to a plain message for an error that is not from the database', function (): void {
    expect(WriteFailureReason::describe(new RuntimeException('boom')))
        ->toBe(__('import_mapping.issue.write_failed'));
});
