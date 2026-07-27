<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\User;
use App\Transfer\Import\BundleImporter;
use Illuminate\Support\Facades\Storage;

it('exports records with related items and imports them into another organisation', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan, $record, $processor] = createExportedBundle($sourceOrganisation, $user);

    expect(Storage::disk('filament')->exists($path))->toBeTrue();

    $result = app(BundleImporter::class)->importZip(
        Storage::disk('filament')->path($path),
        $plan,
        $destinationOrganisation,
        $user,
    );

    expect($result->created)->toBe(4)
        ->and($result->skipped)->toBe(0);

    $imported = AvgResponsibleProcessingRecord::query()
        ->whereBelongsTo($destinationOrganisation)
        ->where('name', 'Verwerking A')
        ->firstOrFail();

    // stable identity is stamped for future conflict detection
    expect($imported->getAttribute('origin_id'))->toBe($record->id->toString())
        ->and($imported->id->toString())->not->toBe($record->id->toString());

    // relations are restored to the newly created counterparts
    $importedProcessor = $imported->processors()->firstOrFail();
    expect($importedProcessor->name)->toBe('Verwerker 1')
        ->and($importedProcessor->id->toString())->not->toBe($processor->id->toString())
        ->and($importedProcessor->getAttribute('organisation_id')->toString())->toBe($destinationOrganisation->id->toString())
        ->and($importedProcessor->address?->city)->toBe('Amsterdam');

    expect($imported->tags()->firstOrFail()->name)->toBe('Label 1')
        ->and($imported->avgGoals()->firstOrFail()->goal)->toBe('Doel 1');

    // the lookup value is recreated in the destination organisation, not referenced across organisations
    $importedServiceId = $imported->getAttribute('avg_responsible_processing_record_service_id');
    $importedService = AvgResponsibleProcessingRecordService::query()->findOrFail($importedServiceId);
    expect($importedService->name)->toBe('Dienst X')
        ->and($importedService->getAttribute('organisation_id')->toString())->toBe($destinationOrganisation->id->toString());

    // owned entities travel along
    expect($imported->fgRemark?->body)->toBe('FG opmerking')
        ->and($imported->remarks()->count())->toBe(1);

    // a fresh entity number is generated in the destination organisation
    expect($imported->getAttribute('entity_number_id'))->not->toBeNull()
        ->and($imported->getAttribute('entity_number_id')->toString())
        ->not->toBe($record->getAttribute('entity_number_id')->toString());
});

it('skips items that already exist when the strategy is skip', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedBundle($sourceOrganisation, $user);

    $importer = app(BundleImporter::class);
    $absolutePath = Storage::disk('filament')->path($path);

    $importer->importZip($absolutePath, $plan, $destinationOrganisation, $user);

    $skipPlan = array_map(static fn (array $item): array => [
        'selected' => true,
        'strategy' => 'skip',
    ], $plan);

    $result = $importer->importZip($absolutePath, $skipPlan, $destinationOrganisation, $user);

    expect($result->created)->toBe(0)
        ->and($result->skipped)->toBe(4)
        ->and(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destinationOrganisation)->count())->toBe(1)
        ->and(Processor::query()->whereBelongsTo($destinationOrganisation)->count())->toBe(1);
});

it('overwrites existing items when the strategy is overwrite', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedBundle($sourceOrganisation, $user);

    $importer = app(BundleImporter::class);
    $absolutePath = Storage::disk('filament')->path($path);

    $importer->importZip($absolutePath, $plan, $destinationOrganisation, $user);

    $imported = AvgResponsibleProcessingRecord::query()
        ->whereBelongsTo($destinationOrganisation)
        ->firstOrFail();
    $imported->update(['name' => 'Lokaal aangepast']);

    $overwritePlan = array_map(static fn (array $item): array => [
        'selected' => true,
        'strategy' => 'overwrite',
    ], $plan);

    $result = $importer->importZip($absolutePath, $overwritePlan, $destinationOrganisation, $user);

    expect($result->overwritten)->toBe(4)
        ->and($imported->refresh()->name)->toBe('Verwerking A')
        ->and(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destinationOrganisation)->count())->toBe(1);
});

it('adds a copy when the strategy is copy', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan, $record] = createExportedBundle($sourceOrganisation, $user);

    $importer = app(BundleImporter::class);
    $absolutePath = Storage::disk('filament')->path($path);

    $importer->importZip($absolutePath, $plan, $destinationOrganisation, $user);

    $copyPlan = $plan;
    $copyPlan[$record->id->toString()]['strategy'] = 'copy';

    $result = $importer->importZip($absolutePath, $copyPlan, $destinationOrganisation, $user);

    expect($result->created)->toBe(1)
        ->and($result->skipped)->toBe(3);

    $copy = AvgResponsibleProcessingRecord::query()
        ->whereBelongsTo($destinationOrganisation)
        ->where('name', 'Verwerking A (kopie)')
        ->firstOrFail();

    expect($copy->getAttribute('origin_id'))->toBeNull()
        ->and($copy->processors()->count())->toBe(1);
});

it('matches existing content by name when there is no origin id', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedBundle($sourceOrganisation, $user);

    // pre-existing processor with the same name in the destination organisation
    $existingProcessor = Processor::factory()->for($destinationOrganisation)->create(['name' => 'Verwerker 1']);

    $result = app(BundleImporter::class)->importZip(
        Storage::disk('filament')->path($path),
        $plan,
        $destinationOrganisation,
        $user,
    );

    expect($result->skipped)->toBe(1)
        ->and(Processor::query()->whereBelongsTo($destinationOrganisation)->count())->toBe(1);

    // the record is linked to the already existing processor
    $imported = AvgResponsibleProcessingRecord::query()
        ->whereBelongsTo($destinationOrganisation)
        ->firstOrFail();
    expect($imported->processors()->firstOrFail()->id->toString())->toBe($existingProcessor->id->toString());
});

it('does not import items that are deselected', function (): void {
    Storage::fake('filament');

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan, , $processor] = createExportedBundle($sourceOrganisation, $user);

    $plan[$processor->id->toString()]['selected'] = false;

    app(BundleImporter::class)->importZip(
        Storage::disk('filament')->path($path),
        $plan,
        $destinationOrganisation,
        $user,
    );

    expect(Processor::query()->whereBelongsTo($destinationOrganisation)->count())->toBe(0);

    $imported = AvgResponsibleProcessingRecord::query()
        ->whereBelongsTo($destinationOrganisation)
        ->firstOrFail();
    expect($imported->processors()->count())->toBe(0);
});
