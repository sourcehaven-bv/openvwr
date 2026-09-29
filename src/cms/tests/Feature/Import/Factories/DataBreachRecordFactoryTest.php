<?php

declare(strict_types=1);

use App\Import\Factories\DataBreachRecordFactory;
use App\Models\Concerns\HasSnapshots;
use App\Models\DataBreachRecord;
use App\Models\Organisation;

it('imports a data breach record', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var DataBreachRecordFactory $factory */
    $factory = $this->app->get(DataBreachRecordFactory::class);
    $dataBreachRecord = $factory->create([
        'Id' => (string) fake()->importId(),
        'Naam' => 'Mail naar verkeerde ontvanger',
        'Type' => 'Definitief',
        'DatumMelding' => '2026-03-04T00:00:00',
        'Samenvatting' => 'Bijlage met cliëntgegevens naar verkeerd adres.',
        'GemeldAp' => 'ja',
        'GemeldFg' => 'nee',
        'GemeldBetrokkene' => 'ja',
    ], $organisation->id);

    expect($dataBreachRecord)->not->toBeNull()
        ->and($dataBreachRecord->name)->toBe('Mail naar verkeerde ontvanger')
        ->and($dataBreachRecord->type)->toBe('Definitief')
        ->and($dataBreachRecord->summary)->toBe('Bijlage met cliëntgegevens naar verkeerd adres.')
        ->and($dataBreachRecord->ap_reported)->toBeTrue()
        ->and($dataBreachRecord->fg_reported)->toBeFalse()
        ->and($dataBreachRecord->reported_to_involved)->toBeTrue()
        ->and($dataBreachRecord->reported_at?->format('Y-m-d'))->toBe('2026-03-04');
});

it('skips the import when a record with the same import_id exists', function (): void {
    $importId = (string) fake()->importId();
    $name = fake()->word();

    $dataBreachRecord = DataBreachRecord::factory()
        ->create([
            'import_id' => $importId,
            'name' => $name,
        ]);

    /** @var DataBreachRecordFactory $factory */
    $factory = $this->app->get(DataBreachRecordFactory::class);
    $result = $factory->create([
        'Id' => $importId,
        'Naam' => fake()->unique()->word(),
    ], $dataBreachRecord->organisation_id);

    $dataBreachRecord->refresh();

    expect($result)->toBeNull()
        ->and($dataBreachRecord->name)->toBe($name);
});

it('leaves optional dates empty instead of failing', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var DataBreachRecordFactory $factory */
    $factory = $this->app->get(DataBreachRecordFactory::class);
    $dataBreachRecord = $factory->create([
        'Id' => (string) fake()->importId(),
        'Naam' => fake()->word(),
        'Type' => 'Voorlopig',
    ], $organisation->id);

    expect($dataBreachRecord?->reported_at)->toBeNull()
        ->and($dataBreachRecord?->discovered_at)->toBeNull()
        ->and($dataBreachRecord?->completed_at)->toBeNull();
});

it('splits a multi-value cell into a list', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var DataBreachRecordFactory $factory */
    $factory = $this->app->get(DataBreachRecordFactory::class);
    $dataBreachRecord = $factory->create([
        'Id' => (string) fake()->importId(),
        'Naam' => fake()->word(),
        'Type' => 'Definitief',
        'CategorieenPersoonsgegevens' => "Naam\nE-mailadres\nAdres en woonplaats",
    ], $organisation->id);

    expect($dataBreachRecord?->personal_data_categories)
        ->toBe(['Naam', 'E-mailadres', 'Adres en woonplaats']);
});

it('accepts a list that is already an array', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var DataBreachRecordFactory $factory */
    $factory = $this->app->get(DataBreachRecordFactory::class);
    $dataBreachRecord = $factory->create([
        'Id' => (string) fake()->importId(),
        'Naam' => fake()->word(),
        'Type' => 'Definitief',
        'CategorieenPersoonsgegevens' => ['Naam', 'Contactgegevens'],
    ], $organisation->id);

    expect($dataBreachRecord?->personal_data_categories)
        ->toBe(['Naam', 'Contactgegevens']);
});

it('does not create a snapshot', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var DataBreachRecordFactory $factory */
    $factory = $this->app->get(DataBreachRecordFactory::class);
    $dataBreachRecord = $factory->create([
        'Id' => (string) fake()->importId(),
        'Naam' => fake()->word(),
        'Type' => 'Definitief',
    ], $organisation->id);

    expect($dataBreachRecord)->not->toBeNull()
        ->and(class_uses_recursive($dataBreachRecord::class))
        ->not->toContain(HasSnapshots::class);
});
