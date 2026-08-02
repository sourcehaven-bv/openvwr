<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\Organisation;
use App\Models\Stakeholder;
use App\Models\User;
use App\Transfer\Import\BundleImporter;
use Illuminate\Support\Facades\Storage;

/**
 * Write a valid transfer zip from raw entity payloads and return the absolute path.
 *
 * @param array<int, array<string, mixed>> $entities
 */
function writeImporterZip(array $entities): string
{
    Storage::fake('transfer');
    $relativePath = sprintf('transfer/imports/%s.zip', fake()->uuid());
    $disk = Storage::disk('transfer');
    $disk->put($relativePath, '');

    $zip = new ZipArchive();
    $zip->open($disk->path($relativePath), ZipArchive::CREATE | ZipArchive::OVERWRITE);

    $manifestEntities = [];
    foreach ($entities as $entity) {
        $zip->addFromString(
            sprintf('entities/%s/%s.json', $entity['type'], $entity['id']),
            json_encode($entity, JSON_THROW_ON_ERROR),
        );
        $manifestEntities[] = ['type' => $entity['type'], 'id' => $entity['id']];
    }

    $zip->addFromString('manifest.json', json_encode([
        'format' => 'openvwr-transfer',
        'version' => 1,
        'entities' => $manifestEntities,
    ], JSON_THROW_ON_ERROR));
    $zip->close();

    return $disk->path($relativePath);
}

it('ignores an owned entity without a valid owner reference', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    $addressId = fake()->uuid();
    $path = writeImporterZip([
        [
            'type' => 'address',
            'id' => $addressId,
            'origin_id' => $addressId,
            'name' => 'Adres',
            'attributes' => ['city' => 'Amsterdam'],
            // no 'owner' key at all
        ],
    ]);

    $result = app(BundleImporter::class)->importZip($path, [], $organisation, $user);

    expect($result->created)->toBe(0);
});

it('does not suffix a copy when the match column value is not a string', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    $originId = fake()->uuid();
    // an existing stakeholder found by origin_id (description is a nullable match column)
    Stakeholder::factory()->for($organisation)->create(['description' => 'Bestaand', 'origin_id' => $originId]);

    $stakeholderId = fake()->uuid();
    $path = writeImporterZip([
        [
            'type' => 'stakeholder',
            'id' => $stakeholderId,
            'origin_id' => $originId,
            'name' => 'Naam',
            // description attribute is null -> suffixCopy returns early
            'attributes' => ['description' => null],
        ],
    ]);

    $plan = [$stakeholderId => ['selected' => true, 'strategy' => 'copy']];

    $result = app(BundleImporter::class)->importZip($path, $plan, $organisation, $user);

    // a copy is created even though the description could not be suffixed
    expect($result->created)->toBe(1)
        ->and(Stakeholder::query()->whereBelongsTo($organisation)->count())->toBe(2);
});

it('ignores an owned entity whose written owner lacks the relation', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    // an avg responsible processing record has no address relation
    $recordId = fake()->uuid();
    $addressId = fake()->uuid();

    $path = writeImporterZip([
        [
            'type' => 'avg_responsible_processing_record',
            'id' => $recordId,
            'origin_id' => $recordId,
            'name' => 'Verwerking',
            'attributes' => ['name' => 'Verwerking', 'has_processors' => false, 'has_systems' => false],
        ],
        [
            'type' => 'address',
            'id' => $addressId,
            'origin_id' => $addressId,
            'name' => 'Adres',
            'attributes' => ['city' => 'Amsterdam'],
            'owner' => ['type' => 'avg_responsible_processing_record', 'id' => $recordId],
        ],
    ]);

    $plan = [$recordId => ['selected' => true, 'strategy' => null]];

    $result = app(BundleImporter::class)->importZip($path, $plan, $organisation, $user);

    // the record is created; the address is silently ignored (no address relation on the owner)
    expect($result->created)->toBe(1);

    $imported = AvgResponsibleProcessingRecord::query()->whereBelongsTo($organisation)->firstOrFail();
    expect($imported->name)->toBe('Verwerking');
});

it('ignores media payloads that are not well formed', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    Storage::fake('media-library');

    $documentId = fake()->uuid();
    $documentTypeId = fake()->uuid();

    $path = writeImporterZip([
        [
            'type' => 'document_type',
            'id' => $documentTypeId,
            'origin_id' => $documentTypeId,
            'name' => 'Soort',
            'attributes' => ['name' => 'Soort'],
        ],
        [
            'type' => 'document',
            'id' => $documentId,
            'origin_id' => $documentId,
            'name' => 'Document',
            'attributes' => ['name' => 'Document', 'document_type_id' => $documentTypeId],
            'media' => [
                // not an array entry -> skipped
                'not-an-array',
                // missing required string fields -> skipped
                ['zip_path' => 123, 'file_name' => 'x.txt', 'collection_name' => 'attachments'],
                // points at a media file that is not in the zip -> contents null, skipped
                ['zip_path' => 'media/missing/x.txt', 'file_name' => 'x.txt', 'collection_name' => 'attachments'],
            ],
        ],
    ]);

    $plan = [$documentId => ['selected' => true, 'strategy' => null]];

    $result = app(BundleImporter::class)->importZip($path, $plan, $organisation, $user);

    expect($result->created)->toBe(1);

    $imported = Document::query()->whereBelongsTo($organisation)->firstOrFail();
    expect($imported->media)->toHaveCount(0);
});

it('ignores a media block that is not a list', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    Storage::fake('media-library');

    $documentId = fake()->uuid();
    $documentTypeId = fake()->uuid();

    $path = writeImporterZip([
        [
            'type' => 'document_type',
            'id' => $documentTypeId,
            'origin_id' => $documentTypeId,
            'name' => 'Soort',
            'attributes' => ['name' => 'Soort'],
        ],
        [
            'type' => 'document',
            'id' => $documentId,
            'origin_id' => $documentId,
            'name' => 'Document',
            'attributes' => ['name' => 'Document', 'document_type_id' => $documentTypeId],
            // a non-array media value -> importMedia returns early
            'media' => 'nope',
        ],
    ]);

    $plan = [$documentId => ['selected' => true, 'strategy' => null]];

    $result = app(BundleImporter::class)->importZip($path, $plan, $organisation, $user);

    expect($result->created)->toBe(1);
});

it('skips a media item with a missing file name', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    Storage::fake('media-library');

    $documentId = fake()->uuid();
    $documentTypeId = fake()->uuid();

    $path = writeImporterZip([
        [
            'type' => 'document_type',
            'id' => $documentTypeId,
            'origin_id' => $documentTypeId,
            'name' => 'Soort',
            'attributes' => ['name' => 'Soort'],
        ],
        [
            'type' => 'document',
            'id' => $documentId,
            'origin_id' => $documentId,
            'name' => 'Document',
            'attributes' => ['name' => 'Document', 'document_type_id' => $documentTypeId],
            // a media entry whose file_name is not a string -> skipped by importMedia
            'media' => [
                ['collection_name' => 'attachments', 'file_name' => null, 'zip_path' => 'media/x/y'],
                'not-even-an-array',
            ],
        ],
    ]);

    $plan = [$documentId => ['selected' => true, 'strategy' => null]];

    $result = app(BundleImporter::class)->importZip($path, $plan, $organisation, $user);

    $document = Document::query()->whereBelongsTo($organisation)->firstOrFail();

    expect($result->created)->toBe(1)
        ->and($document->media()->count())->toBe(0);
});
