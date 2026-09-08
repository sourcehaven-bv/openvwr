<?php

declare(strict_types=1);

use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use App\Import\Mapping\MappingField;
use App\Import\Mapping\MappingProfile;
use App\Import\Mapping\MappingProfileRepository;
use App\Models\DataBreachRecord;
use App\Models\Organisation;

function simpleProfile(): MappingProfile
{
    return new MappingProfile(
        DataBreachRecord::class,
        [new MappingField('Naam', 'name', MappingTransform::Text, MappingConfidence::Exact)],
        ['Melder'],
    );
}

it('recognises a sheet it has seen before', function (): void {
    $organisation = Organisation::factory()->create();
    $headers = ['Naam', 'Melder'];

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $repository->store('Zenya-export', simpleProfile(), $headers, $organisation->id);

    $found = $repository->findByFingerprint(MappingProfile::fingerprint($headers), $organisation->id);

    expect($found?->name)->toBe('Zenya-export');
});

it('does not recognise a different set of columns', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $repository->store('Zenya-export', simpleProfile(), ['Naam', 'Melder'], $organisation->id);

    expect($repository->findByFingerprint(MappingProfile::fingerprint(['Heel', 'Anders']), $organisation->id))
        ->toBeNull();
});

it('stores an edit as a new version instead of overwriting', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $first = $repository->store('Zenya-export', simpleProfile(), ['Naam'], $organisation->id);
    $second = $repository->store('Zenya-export', simpleProfile(), ['Naam'], $organisation->id);

    expect($first->version)->toBe(1)
        ->and($second->version)->toBe(2)
        ->and($first->exists)->toBeTrue();
});

it('shows which columns changed since the profile was saved', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $profile = $repository->store('Zenya-export', simpleProfile(), ['Naam', 'Melder'], $organisation->id);

    $diff = $repository->diff($profile, ['Naam', 'Afdeling']);

    expect($diff['added'])->toBe(['Afdeling'])
        ->and($diff['removed'])->toBe(['Melder']);
});

it('round-trips a stored mapping', function (): void {
    $organisation = Organisation::factory()->create();

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $stored = $repository->store('Zenya-export', simpleProfile(), ['Naam'], $organisation->id);

    $restored = $stored->fresh()?->toMappingProfile();

    expect($restored?->target)->toBe(DataBreachRecord::class)
        ->and($restored?->fields[0]->source)->toBe('Naam')
        ->and($restored?->fields[0]->target)->toBe('name')
        ->and($restored?->unmapped)->toBe(['Melder']);
});

it('does not recognise a profile saved by another organisation', function (): void {
    // Two organisations importing from the same source system upload sheets
    // with identical columns; one must not inherit the other's choices.
    $one = Organisation::factory()->create();
    $two = Organisation::factory()->create();
    $headers = ['Naam', 'Melder'];

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $repository->store('Zenya-export', simpleProfile(), $headers, $one->id);

    expect($repository->findByFingerprint(MappingProfile::fingerprint($headers), $two->id))->toBeNull()
        ->and($repository->findCandidates(DataBreachRecord::class, $two->id))->toBe([])
        ->and($repository->findCandidates(DataBreachRecord::class, $one->id))->toHaveCount(1);
});

it('numbers versions per organisation', function (): void {
    $one = Organisation::factory()->create();
    $two = Organisation::factory()->create();

    /** @var MappingProfileRepository $repository */
    $repository = $this->app->get(MappingProfileRepository::class);
    $repository->store('Maandelijks', simpleProfile(), ['Naam'], $one->id);
    $repository->store('Maandelijks', simpleProfile(), ['Naam'], $one->id);
    $first = $repository->store('Maandelijks', simpleProfile(), ['Naam'], $two->id);

    expect($first->version)->toBe(1);
});

it('recognises the same layout regardless of casing and spacing', function (): void {
    expect(MappingProfile::fingerprint(['Datum melding', 'Naam']))
        ->toBe(MappingProfile::fingerprint(['naam', 'Datum  Melding ']));
});

it('restores a profile from its stored shape, with and without optional keys', function (): void {
    $profile = MappingProfile::fromArray([
        'target' => DataBreachRecord::class,
        'identity' => 'Meldnummer',
        'fields' => [
            ['source' => 'Naam', 'target' => 'name'],
            [
                'source' => 'Gemeld',
                'target' => 'ap_reported_at',
                'transform' => MappingTransform::BooleanToDate->value,
                'confidence' => MappingConfidence::Exact->value,
                'true_date' => '2026-01-01T00:00:00',
                'relation' => 'processors',
            ],
        ],
    ]);

    expect($profile->identity)->toBe('Meldnummer')
        ->and($profile->fields[0]->transform)->toBe(MappingTransform::Text)
        ->and($profile->fields[0]->confidence)->toBe(MappingConfidence::Manual)
        ->and($profile->fields[1]->transform)->toBe(MappingTransform::BooleanToDate)
        ->and($profile->fields[1]->trueDate)->toBe('2026-01-01T00:00:00')
        ->and($profile->fields[1]->relation)->toBe('processors');
});
