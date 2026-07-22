<?php

declare(strict_types=1);

use App\Models\Organisation;
use App\Models\Processor;
use App\Transfer\Import\ImportMatcher;
use App\Transfer\TransferEntityType;

it('never matches owned or lookup entities', function (): void {
    $organisation = Organisation::factory()->create();
    $matcher = new ImportMatcher();

    expect($matcher->match(TransferEntityType::ADDRESS, ['origin_id' => 'x'], $organisation))->toBeNull()
        ->and($matcher->match(TransferEntityType::DOCUMENT_TYPE, ['origin_id' => 'x'], $organisation))->toBeNull();
});

it('matches by origin id first', function (): void {
    $organisation = Organisation::factory()->create();
    $originId = fake()->uuid();
    $existing = Processor::factory()->for($organisation)->create([
        'name' => 'Verwerker',
        'origin_id' => $originId,
    ]);

    $matcher = new ImportMatcher();
    $entity = ['origin_id' => $originId, 'attributes' => ['name' => 'Andere naam']];

    expect($matcher->match(TransferEntityType::PROCESSOR, $entity, $organisation)?->id->toString())
        ->toBe($existing->id->toString());
});

it('falls back to matching by name when there is no origin id', function (): void {
    $organisation = Organisation::factory()->create();
    $existing = Processor::factory()->for($organisation)->create(['name' => 'Verwerker uniek']);

    $matcher = new ImportMatcher();
    $entity = ['attributes' => ['name' => 'Verwerker uniek']];

    expect($matcher->match(TransferEntityType::PROCESSOR, $entity, $organisation)?->id->toString())
        ->toBe($existing->id->toString());
});

it('returns null when the origin id is empty or not a string', function (): void {
    $organisation = Organisation::factory()->create();
    $matcher = new ImportMatcher();

    // empty origin id and no attributes to match on
    expect($matcher->match(TransferEntityType::PROCESSOR, ['origin_id' => ''], $organisation))->toBeNull()
        ->and($matcher->match(TransferEntityType::PROCESSOR, ['origin_id' => 123], $organisation))->toBeNull();
});

it('returns null when the attributes payload is not an array', function (): void {
    $organisation = Organisation::factory()->create();
    $matcher = new ImportMatcher();

    expect($matcher->match(TransferEntityType::PROCESSOR, ['attributes' => 'not-an-array'], $organisation))->toBeNull();
});

it('returns null when the match value is empty or not a string', function (): void {
    $organisation = Organisation::factory()->create();
    $matcher = new ImportMatcher();

    expect($matcher->match(TransferEntityType::PROCESSOR, ['attributes' => ['name' => '']], $organisation))->toBeNull()
        ->and($matcher->match(TransferEntityType::PROCESSOR, ['attributes' => ['name' => 42]], $organisation))->toBeNull();
});
