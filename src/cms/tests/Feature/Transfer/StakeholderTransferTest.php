<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Stakeholder;
use App\Models\StakeholderDataItem;
use App\Models\User;
use App\Transfer\Export\BundleExporter;
use App\Transfer\Import\BundleImporter;
use App\Transfer\TransferEntityType;
use Illuminate\Support\Facades\Storage;

it('exports a stakeholder with its data items and imports them into another organisation', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    $record = AvgResponsibleProcessingRecord::factory()->for($sourceOrganisation)->create([
        'name' => 'Verwerking met belanghebbende',
        'has_processors' => true,
        'has_systems' => true,
    ]);

    $stakeholder = Stakeholder::factory()->for($sourceOrganisation)->create(['description' => 'Belanghebbende A']);
    $dataItem = StakeholderDataItem::factory()->for($sourceOrganisation)->create(['description' => 'Gegeven 1']);
    $stakeholder->stakeholderDataItems()->attach($dataItem);
    $record->stakeholders()->attach($stakeholder);

    $path = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['stakeholders' => [$stakeholder->id->toString()]],
        $sourceOrganisation,
    );

    $plan = [
        $record->id->toString() => ['selected' => true, 'strategy' => null],
        $stakeholder->id->toString() => ['selected' => true, 'strategy' => null],
        $dataItem->id->toString() => ['selected' => true, 'strategy' => null],
    ];

    $result = app(BundleImporter::class)->import(
        Storage::disk('filament')->path($path),
        $plan,
        $destinationOrganisation,
        $user,
    );

    expect($result->created)->toBeGreaterThanOrEqual(2);

    $importedStakeholder = Stakeholder::query()
        ->whereBelongsTo($destinationOrganisation)
        ->where('description', 'Belanghebbende A')
        ->firstOrFail();

    expect($importedStakeholder->stakeholderDataItems()->firstOrFail()->description)->toBe('Gegeven 1');
});

it('does not export related items that were not selected', function (): void {
    Storage::fake('filament');

    $organisation = Organisation::factory()->create();

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create([
        'name' => 'Verwerking',
        'has_processors' => true,
        'has_systems' => true,
    ]);

    $processor = Processor::factory()->for($organisation)->create(['name' => 'Niet geselecteerd']);
    $record->processors()->attach($processor);

    // no related ids selected at all -> the processor is skipped during collection
    $path = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        $organisation,
    );

    $zip = new ZipArchive();
    $zip->open(Storage::disk('filament')->path($path));

    $hasProcessorEntity = false;
    for ($i = 0; $i < $zip->count(); $i++) {
        if (str_contains((string) $zip->getNameIndex($i), 'entities/processor/')) {
            $hasProcessorEntity = true;
        }
    }
    $zip->close();

    expect($hasProcessorEntity)->toBeFalse();
});

it('deduplicates an entity reached through two exported records', function (): void {
    Storage::fake('filament');

    $organisation = Organisation::factory()->create();

    $recordA = AvgResponsibleProcessingRecord::factory()->for($organisation)->create([
        'name' => 'Verwerking A',
        'has_processors' => true,
        'has_systems' => true,
    ]);
    $recordB = AvgResponsibleProcessingRecord::factory()->for($organisation)->create([
        'name' => 'Verwerking B',
        'has_processors' => true,
        'has_systems' => true,
    ]);

    $shared = Processor::factory()->for($organisation)->create(['name' => 'Gedeeld']);
    $recordA->processors()->attach($shared);
    $recordB->processors()->attach($shared);

    $path = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$recordA->id->toString(), $recordB->id->toString()],
        ['processors' => [$shared->id->toString()]],
        $organisation,
    );

    $zip = new ZipArchive();
    $zip->open(Storage::disk('filament')->path($path));

    $processorEntities = 0;
    for ($i = 0; $i < $zip->count(); $i++) {
        if (str_contains((string) $zip->getNameIndex($i), 'entities/processor/')) {
            $processorEntities++;
        }
    }
    $zip->close();

    // the shared processor is only written once
    expect($processorEntities)->toBe(1);
});
