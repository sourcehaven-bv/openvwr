<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\User;
use App\Transfer\ConflictStrategy;
use App\Transfer\CrossOrgCopier;
use App\Transfer\Export\BundleBuilder;
use App\Transfer\Import\EditDetector;
use App\Transfer\Import\PreviewBuilder;
use App\Transfer\TransferEntityType;
use Carbon\CarbonImmutable;

function copyOnce(Organisation $source, Organisation $destination, User $user, AvgResponsibleProcessingRecord $record): void
{
    app(CrossOrgCopier::class)->copy(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['processors' => Processor::query()->whereBelongsTo($source)->pluck('id')->map->toString()->all()],
        [
            $record->id->toString() => ['selected' => true, 'strategy' => 'overwrite'],
            ...Processor::query()->whereBelongsTo($source)->get()
                ->mapWithKeys(static fn ($p): array => [$p->id->toString() => ['selected' => true, 'strategy' => 'overwrite']])
                ->all(),
        ],
        $source,
        $destination,
        $user,
    );
}

function previewFor(BundleBuilder $builder, PreviewBuilder $preview, Organisation $source, Organisation $destination, AvgResponsibleProcessingRecord $record): array
{
    $bundle = $builder->build(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['processors' => Processor::query()->whereBelongsTo($source)->pluck('id')->map->toString()->all()],
        $source,
    );

    return $preview->build($bundle, $destination);
}

it('flags a freshly copied record as not edited and defaults to silent overwrite', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);
    [$record] = seedCopyableRecord($source);

    copyOnce($source, $destination, $user, $record);

    $preview = previewFor(app(BundleBuilder::class), app(PreviewBuilder::class), $source, $destination, $record);
    $recordPreview = $preview[$record->id->toString()];

    expect($recordPreview['has_match'])->toBeTrue()
        ->and($recordPreview['needs_decision'])->toBeFalse()
        ->and($recordPreview['strategy'])->toBe(ConflictStrategy::OVERWRITE->value);
});

it('flags a locally edited copy as needing a decision and defaults to skip', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);
    [$record] = seedCopyableRecord($source);

    copyOnce($source, $destination, $user, $record);

    // Edit the copy after it was synced.
    $copy = AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->firstOrFail();
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinute());
    $copy->update(['name' => 'Lokaal aangepast']);
    CarbonImmutable::setTestNow();

    $preview = previewFor(app(BundleBuilder::class), app(PreviewBuilder::class), $source, $destination, $record);
    $recordPreview = $preview[$record->id->toString()];

    expect($recordPreview['needs_decision'])->toBeTrue()
        ->and($recordPreview['strategy'])->toBe(ConflictStrategy::SKIP->value);
});

it('detects a relation-only edit on a copy as edited', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);
    [$record] = seedCopyableRecord($source);

    copyOnce($source, $destination, $user, $record);

    $copy = AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->firstOrFail();

    // Attach a new processor to the copy *after* sync — bumps only the pivot timestamp,
    // not the record's updated_at, so this exercises the relation branch of EditDetector.
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinute());
    $extra = Processor::factory()->for($destination)->create(['name' => 'Extra verwerker']);
    $copy->processors()->attach($extra);
    CarbonImmutable::setTestNow();

    expect(app(EditDetector::class)->isEditedSinceSync($copy->refresh()))->toBeTrue();
});

it('treats a never-synced match as edited so the user is asked', function (): void {
    $destination = Organisation::factory()->create();
    // A record that exists but was never synced (last_synced_at is null).
    $record = AvgResponsibleProcessingRecord::factory()->for($destination)->create([
        'has_processors' => true,
        'has_systems' => true,
    ]);

    expect($record->getAttribute('last_synced_at'))->toBeNull()
        ->and(app(EditDetector::class)->isEditedSinceSync($record))->toBeTrue();
});

it('re-copies an untouched copy without creating duplicates', function (): void {
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = copyableUser($source, $destination);
    [$record] = seedCopyableRecord($source);

    copyOnce($source, $destination, $user, $record);
    $countAfterFirst = AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->count();

    // Re-copy with the smart defaults: untouched → overwrite in place.
    copyOnce($source, $destination, $user, $record);
    $countAfterSecond = AvgResponsibleProcessingRecord::query()->whereBelongsTo($destination)->count();

    expect($countAfterSecond)->toBe($countAfterFirst)
        ->and($countAfterFirst)->toBe(1);
});
