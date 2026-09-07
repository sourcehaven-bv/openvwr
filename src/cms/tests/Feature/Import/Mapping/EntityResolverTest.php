<?php

declare(strict_types=1);

use App\Import\Mapping\EntityResolver;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\System;

it('creates an entity that does not exist yet', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $processor = $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);

    expect($processor?->getAttribute('name'))->toBe('Firma A')
        ->and($resolver->created()[Processor::class])->toBe(['Firma A']);
});

it('reuses the same record for the same name', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $first = $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);
    $second = $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);

    expect($second?->getKey())->toBe($first?->getKey())
        ->and(Processor::query()->where('name', 'Firma A')->count())->toBe(1);
});

it('reuses a record that already existed before the import', function (): void {
    $organisation = Organisation::factory()->create();
    $existing = Processor::factory()->create([
        'organisation_id' => $organisation->id,
        'name' => 'Firma A',
    ]);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolved = $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);

    expect($resolved?->getKey())->toBe($existing->getKey())
        ->and($resolver->created())->toBeEmpty();
});

it('matches through casing, spacing and legal form', function (string $variant): void {
    $organisation = Organisation::factory()->create();
    $existing = Processor::factory()->create([
        'organisation_id' => $organisation->id,
        'name' => 'Firma A',
    ]);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolved = $resolver->resolve(Processor::class, 'name', $variant, $organisation->id);

    expect($resolved?->getKey())->toBe($existing->getKey());
})->with(['firma a', 'FIRMA A', 'Firma  A', 'Firma A B.V.', 'Firma A bv']);

it('reports a match that needed normalising', function (): void {
    $organisation = Organisation::factory()->create();
    Processor::factory()->create(['organisation_id' => $organisation->id, 'name' => 'Firma A']);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolver->resolve(Processor::class, 'name', 'Firma A B.V.', $organisation->id);

    expect($resolver->fuzzyMatches()[Processor::class][0])
        ->toBe(['source' => 'Firma A B.V.', 'matched' => 'Firma A']);
});

it('does not match merely similar names', function (): void {
    $organisation = Organisation::factory()->create();
    Processor::factory()->create(['organisation_id' => $organisation->id, 'name' => 'Zorggroep Noord']);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolver->resolve(Processor::class, 'name', 'Zorggroep Oost', $organisation->id);

    expect(Processor::query()->where('organisation_id', $organisation->id)->count())->toBe(2);
});

it('keeps entities of different organisations apart', function (): void {
    $one = Organisation::factory()->create();
    $two = Organisation::factory()->create();
    $existing = Processor::factory()->create(['organisation_id' => $one->id, 'name' => 'Firma A']);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolved = $resolver->resolve(Processor::class, 'name', 'Firma A', $two->id);

    expect($resolved?->getAttribute('organisation_id')?->toString())->toBe($two->id->toString())
        ->and((string) $resolved?->getKey())->not->toBe((string) $existing->getKey());
});

it('uses the name attribute the model actually has', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $system = $resolver->resolve(System::class, 'description', 'Zorgdossier', $organisation->id);

    expect($system?->getAttribute('description'))->toBe('Zorgdossier');
});

it('uses the oldest record when the name occurs more than once', function (): void {
    $organisation = Organisation::factory()->create();

    // The register does not enforce unique names, so duplicates exist in
    // practice; the choice must at least be predictable.
    $oldest = Processor::factory()->create([
        'organisation_id' => $organisation->id,
        'name' => 'Firma A',
        'created_at' => now()->subYear(),
    ]);
    Processor::factory()->create([
        'organisation_id' => $organisation->id,
        'name' => 'Firma A',
        'created_at' => now(),
    ]);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolved = $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);

    expect($resolved?->getKey())->toBe($oldest->getKey());
});

it('reports that a name matched more than one record', function (): void {
    $organisation = Organisation::factory()->create();

    foreach (['Firma A', 'Firma A'] as $name) {
        Processor::factory()->create(['organisation_id' => $organisation->id, 'name' => $name]);
    }

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);

    expect($resolver->ambiguousMatches()[Processor::class]['Firma A'])->toBe(2);
});

it('says nothing about ambiguity when the name is unique', function (): void {
    $organisation = Organisation::factory()->create();
    Processor::factory()->create(['organisation_id' => $organisation->id, 'name' => 'Firma A']);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolver->resolve(Processor::class, 'name', 'Firma A', $organisation->id);

    expect($resolver->ambiguousMatches())->toBeEmpty();
});

it('reports duplicates found through normalised spelling too', function (): void {
    $organisation = Organisation::factory()->create();
    Processor::factory()->create(['organisation_id' => $organisation->id, 'name' => 'Firma A']);
    Processor::factory()->create(['organisation_id' => $organisation->id, 'name' => 'firma a']);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $resolver->resolve(Processor::class, 'name', 'Firma A B.V.', $organisation->id);

    expect($resolver->ambiguousMatches()[Processor::class]['Firma A B.V.'])->toBe(2);
});

it('ignores a blank value', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);

    expect($resolver->resolve(Processor::class, 'name', '   ', $organisation->id))->toBeNull()
        ->and($resolver->created())->toBeEmpty();
});

it('ignores existing records without a name when matching on spelling', function (): void {
    $organisation = Organisation::factory()->create();
    System::factory()->create(['organisation_id' => $organisation->id, 'description' => null]);

    /** @var EntityResolver $resolver */
    $resolver = $this->app->get(EntityResolver::class);
    $system = $resolver->resolve(System::class, 'description', 'Zorgdossier', $organisation->id);

    expect($system?->getAttribute('description'))->toBe('Zorgdossier');
});
