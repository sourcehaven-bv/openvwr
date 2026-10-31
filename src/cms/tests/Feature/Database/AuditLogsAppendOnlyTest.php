<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    DB::table('audit_logs')->insert([
        'email' => 'auditor@example.com',
        'event_code' => '091111',
        'created_at' => now(),
    ]);
});

it('allows inserts', function (): void {
    expect(DB::table('audit_logs')->where('email', 'auditor@example.com')->count())->toBe(1);
});

it('rejects changes to audit_logs', function (Closure $change, string $operation): void {
    // A savepoint keeps the test transaction usable after the error.
    expect(static fn () => DB::transaction($change))
        ->toThrow(QueryException::class, sprintf('audit_logs is append-only: %s is not allowed', $operation));

    expect(DB::table('audit_logs')->where('email', 'auditor@example.com')->value('event_code'))->toBe('091111');
})->with([
    'update' => [static fn () => DB::table('audit_logs')->update(['event_code' => '000000']), 'UPDATE'],
    'delete' => [static fn () => DB::table('audit_logs')->delete(), 'DELETE'],
    'truncate' => [static fn () => DB::statement('TRUNCATE audit_logs'), 'TRUNCATE'],
]);
