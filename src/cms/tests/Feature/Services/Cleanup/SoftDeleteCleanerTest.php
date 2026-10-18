<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Models\AdminLogEntry;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\User;
use App\Services\Cleanup\SoftDeleteCleaner;
use App\Services\Cleanup\SoftDeleteCleanupOrder;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('removes the associated files from storage', function (): void {
    Storage::fake('media-library');

    $document = Document::factory()->for(Organisation::factory())->create();
    $document->addMediaFromString('text bytes')
        ->usingFileName('beleid.txt')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $media = $document->refresh()->getFirstMedia(MediaGroup::ATTACHMENTS->value);
    $path = $media->getPathRelativeToRoot();
    Storage::disk('media-library')->assertExists($path);

    $document->delete();

    app(SoftDeleteCleaner::class)->forceDeleteRecord($document->fresh());

    Storage::disk('media-library')->assertMissing($path);
    expect(Document::withTrashed()->find($document->id))->toBeNull();
});

it('removes the media rows along with the record', function (): void {
    Storage::fake('media-library');

    $document = Document::factory()->for(Organisation::factory())->create();
    $document->addMediaFromString('text bytes')
        ->usingFileName('beleid.txt')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $document->delete();

    app(SoftDeleteCleaner::class)->forceDeleteRecord($document->fresh());

    expect(DB::table('media')->where('model_id', $document->id->toString())->count())
        ->toBe(0);
});

it('force deletes a record regardless of the retention period', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create();
    $user->delete();

    app(SoftDeleteCleaner::class)->forceDeleteRecord($user->fresh());

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('refuses to force delete a model that does not soft delete', function (): void {
    $adminLogEntry = AdminLogEntry::factory()->create();

    expect(static fn () => app(SoftDeleteCleaner::class)->forceDeleteRecord($adminLogEntry))
        ->toThrow(InvalidArgumentException::class);
});

// Het auditlog blijft bewust bestaan: de verantwoordingsplicht (art. 5 lid 2
// AVG) vraagt om een spoor dat aantoont dát er correct is opgeruimd. Zie de
// toelichting in SoftDeleteCleaner::cleanupExpired().
it('keeps the admin log when records are permanently deleted', function (): void {
    config()->set('cleanup.retention_days', 90);

    $adminLogEntry = AdminLogEntry::factory()->create();

    User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(120),
    ]);

    app(SoftDeleteCleaner::class)->cleanupExpired();

    expect(AdminLogEntry::find($adminLogEntry->id))->not->toBeNull();
});

it('does not touch the audits table', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(120),
    ]);

    DB::table('audits')->insert([
        'id' => fake()->uuid(),
        'event' => 'deleted',
        'auditable_type' => User::class,
        'auditable_id' => $user->id->toString(),
        'created_at' => CarbonImmutable::now(),
        'updated_at' => CarbonImmutable::now(),
    ]);

    app(SoftDeleteCleaner::class)->cleanupExpired();

    expect(User::withTrashed()->find($user->id))->toBeNull()
        ->and(DB::table('audits')->where('auditable_id', $user->id->toString())->count())->toBe(1);
});

it('honours the configured batch size', function (): void {
    config()->set('cleanup.retention_days', 90);
    config()->set('cleanup.batch_size', 1);

    $deletedAt = CarbonImmutable::now()->subDays(120);
    User::factory()->count(3)->create(['deleted_at' => $deletedAt]);

    $deleted = app(SoftDeleteCleaner::class)->cleanupExpired();

    expect($deleted[User::class])->toBe(1)
        ->and(User::onlyTrashed()->count())->toBe(2);
});

it('returns the number of deleted records per model', function (): void {
    config()->set('cleanup.retention_days', 90);
    config()->set('cleanup.batch_size', 0);

    $deletedAt = CarbonImmutable::now()->subDays(120);
    User::factory()->count(2)->create(['deleted_at' => $deletedAt]);

    $deleted = app(SoftDeleteCleaner::class)->cleanupExpired();

    expect($deleted[User::class])->toBe(2);
});

it('accepts an explicit reference moment', function (): void {
    config()->set('cleanup.retention_days', 90);

    $user = User::factory()->create([
        'deleted_at' => CarbonImmutable::now()->subDays(10),
    ]);

    // Honderd dagen later gerekend is dezelfde soft delete wél verlopen.
    app(SoftDeleteCleaner::class)->cleanupExpired(CarbonImmutable::now()->addDays(100));

    expect(User::withTrashed()->find($user->id))->toBeNull();
});

it('cascades an organisation without leaving its documents behind', function (): void {
    config()->set('cleanup.retention_days', 90);
    config()->set('cleanup.batch_size', 0);

    $deletedAt = CarbonImmutable::now()->subDays(120);

    $organisation = Organisation::factory()->create(['deleted_at' => $deletedAt]);
    $document = Document::factory()->for($organisation)->create(['deleted_at' => $deletedAt]);

    app(SoftDeleteCleaner::class)->cleanupExpired();

    expect(Document::withTrashed()->find($document->id))->toBeNull()
        ->and(Organisation::withTrashed()->find($organisation->id))->toBeNull();
});

// Bewaakt dat de lijst geen model bevat dat helemaal niet soft-delete: een
// forceDelete daarop zou records weggooien die nooit als verwijderd zijn
// gemarkeerd.
it('only lists models that actually soft delete', function (): void {
    foreach (SoftDeleteCleanupOrder::models() as $modelClass) {
        expect(in_array(SoftDeletes::class, class_uses_recursive($modelClass), true))
            ->toBeTrue();
    }
});

it('lists every self referencing model in the deletion order', function (): void {
    foreach (SoftDeleteCleanupOrder::SELF_REFERENCING as $modelClass) {
        expect(SoftDeleteCleanupOrder::models())->toContain($modelClass);
    }
});
