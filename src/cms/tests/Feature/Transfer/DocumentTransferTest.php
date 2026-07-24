<?php

declare(strict_types=1);

use App\Enums\Media\MediaGroup;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\User;
use App\Transfer\Export\BundleExporter;
use App\Transfer\Import\BundleImporter;
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
