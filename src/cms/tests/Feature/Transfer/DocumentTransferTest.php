<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\User;
use App\Transfer\Export\BundleBuilder;
use App\Transfer\Export\BundleExporter;
use App\Transfer\Import\BundleImporter;
use App\Transfer\Import\PreviewBuilder;
use App\Transfer\TransferEntityType;
use Illuminate\Support\Facades\Storage;

/**
 * @return array{string, array<string, array{selected: bool, strategy: ?string}>, Document}
 */
function createExportedDocumentBundle(Organisation $organisation): array
{
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create([
        'name' => 'Verwerking met document',
        'has_processors' => true,
        'has_systems' => true,
    ]);

    $document = Document::factory()->for($organisation)->create(['name' => 'Beleid']);
    $document->addMediaFromString('text bytes')
        ->usingFileName('beleid.txt')
        ->usingName('Beleidsdocument')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $record->documents()->attach($document);

    $path = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['documents' => [$document->id->toString()]],
        $organisation,
    );

    $plan = [
        $record->id->toString() => ['selected' => true, 'strategy' => null],
        $document->id->toString() => ['selected' => true, 'strategy' => null],
    ];

    return [$path, $plan, $document];
}

it('exports a document with its media and imports it into another organisation', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedDocumentBundle($sourceOrganisation);

    $result = app(BundleImporter::class)->importZip(
        Storage::disk('filament')->path($path),
        $plan,
        $destinationOrganisation,
        $user,
    );

    expect($result->created)->toBe(2);

    $imported = Document::query()
        ->whereBelongsTo($destinationOrganisation)
        ->where('name', 'Beleid')
        ->firstOrFail();

    $media = $imported->media->first();
    expect($media)->not->toBeNull()
        ->and($media->file_name)->toBe('beleid.txt')
        ->and($media->name)->toBe('Beleidsdocument')
        ->and($media->collection_name)->toBe(MediaGroup::ATTACHMENTS->value);
});

it('imports media onto an overwritten document that has none yet', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan, $document] = createExportedDocumentBundle($sourceOrganisation);
    $absolutePath = Storage::disk('filament')->path($path);
    $importer = app(BundleImporter::class);

    // pre-existing document in the destination, matched by name, without media
    $existing = Document::factory()->for($destinationOrganisation)->create(['name' => 'Beleid']);
    expect($existing->media)->toHaveCount(0);

    $overwritePlan = $plan;
    $overwritePlan[$document->id->toString()]['strategy'] = 'overwrite';

    $result = $importer->importZip($absolutePath, $overwritePlan, $destinationOrganisation, $user);

    expect($result->overwritten)->toBe(1);

    $existing->refresh()->load('media');
    expect($existing->media->first()?->file_name)->toBe('beleid.txt');
});

it('refreshes a stale attachment on overwrite when the source file changed', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $importer = app(BundleImporter::class);

    // First copy: destination gets the original file bytes.
    [$firstPath, $firstPlan, $document] = createExportedDocumentBundle($sourceOrganisation);
    $importer->importZip(Storage::disk('filament')->path($firstPath), $firstPlan, $destinationOrganisation, $user);

    $copy = Document::query()->whereBelongsTo($destinationOrganisation)->where('name', 'Beleid')->firstOrFail();
    expect($copy->getFirstMedia(MediaGroup::ATTACHMENTS->value)?->getPathRelativeToRoot())
        ->and(Storage::disk('media-library')->get($copy->getFirstMedia(MediaGroup::ATTACHMENTS->value)->getPathRelativeToRoot()))
        ->toBe('text bytes');

    // Source file changes; re-export and overwrite the existing copy.
    $document->clearMediaCollection(MediaGroup::ATTACHMENTS->value);
    $document->addMediaFromString('updated bytes')
        ->usingFileName('beleid.txt')
        ->usingName('Beleidsdocument')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $secondPath = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [AvgResponsibleProcessingRecord::query()->whereBelongsTo($sourceOrganisation)->firstOrFail()->id->toString()],
        ['documents' => [$document->id->toString()]],
        $sourceOrganisation,
    );

    $overwritePlan = [
        AvgResponsibleProcessingRecord::query()->whereBelongsTo($sourceOrganisation)->firstOrFail()->id->toString()
            => ['selected' => true, 'strategy' => 'overwrite'],
        $document->id->toString() => ['selected' => true, 'strategy' => 'overwrite'],
    ];

    $result = $importer->importZip(Storage::disk('filament')->path($secondPath), $overwritePlan, $destinationOrganisation, $user);

    expect($result->overwritten)->toBe(2);

    $copy->refresh()->load('media');
    $media = $copy->getFirstMedia(MediaGroup::ATTACHMENTS->value);
    expect($media)->not->toBeNull()
        ->and($copy->media)->toHaveCount(1)
        ->and(Storage::disk('media-library')->get($media->getPathRelativeToRoot()))->toBe('updated bytes');
});

it('leaves an unchanged attachment in place on overwrite', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $importer = app(BundleImporter::class);

    [$firstPath, $firstPlan] = createExportedDocumentBundle($sourceOrganisation);
    $importer->importZip(Storage::disk('filament')->path($firstPath), $firstPlan, $destinationOrganisation, $user);

    $copy = Document::query()->whereBelongsTo($destinationOrganisation)->where('name', 'Beleid')->firstOrFail();
    $originalUuid = $copy->getFirstMedia(MediaGroup::ATTACHMENTS->value)?->uuid;

    // Overwrite again from the identical source: the file bytes match by content_hash,
    // so the existing media row is kept rather than cleared and re-added.
    $overwritePlan = $firstPlan;
    foreach (array_keys($overwritePlan) as $id) {
        $overwritePlan[$id]['strategy'] = 'overwrite';
    }

    $importer->importZip(Storage::disk('filament')->path($firstPath), $overwritePlan, $destinationOrganisation, $user);

    $copy->refresh()->load('media');
    expect($copy->media)->toHaveCount(1)
        ->and($copy->getFirstMedia(MediaGroup::ATTACHMENTS->value)?->uuid)->toBe($originalUuid);
});

/**
 * @return array{Organisation, Organisation, Document, AvgResponsibleProcessingRecord}
 */
function copyDocumentOnceForPreview(): array
{
    $source = Organisation::factory()->create();
    $destination = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan, $document] = createExportedDocumentBundle($source);
    app(BundleImporter::class)->importZip(Storage::disk('filament')->path($path), $plan, $destination, $user);

    $record = AvgResponsibleProcessingRecord::query()->whereBelongsTo($source)->firstOrFail();

    return [$source, $destination, $document, $record];
}

function previewDocumentItem(Organisation $source, Organisation $destination, Document $document, AvgResponsibleProcessingRecord $record): array
{
    $bundle = app(BundleBuilder::class)->build(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['documents' => [$document->id->toString()]],
        $source,
    );

    return app(PreviewBuilder::class)->build($bundle, $destination)[$document->id->toString()];
}

it('flags a document whose source attachment changed as needing a decision', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    [$source, $destination, $document, $record] = copyDocumentOnceForPreview();

    // Source file changes after the copy: the destination copy is now stale.
    $document->clearMediaCollection(MediaGroup::ATTACHMENTS->value);
    $document->addMediaFromString('updated bytes')
        ->usingFileName('beleid.txt')
        ->usingName('Beleidsdocument')
        ->toMediaCollection(MediaGroup::ATTACHMENTS->value);

    $item = previewDocumentItem($source, $destination, $document, $record);

    expect($item['has_match'])->toBeTrue()
        ->and($item['unchanged'])->toBeFalse()
        ->and($item['needs_decision'])->toBeTrue();
});

it('flags a document with an identical attachment as unchanged', function (): void {
    Storage::fake('filament');
    Storage::fake('media-library');

    [$source, $destination, $document, $record] = copyDocumentOnceForPreview();

    // Nothing changed on either side: the attachment matches by content_hash.
    $item = previewDocumentItem($source, $destination, $document, $record);

    expect($item['has_match'])->toBeTrue()
        ->and($item['unchanged'])->toBeTrue()
        ->and($item['needs_decision'])->toBeFalse();
});
