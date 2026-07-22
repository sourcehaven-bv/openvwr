<?php

declare(strict_types=1);

use App\Models\Organisation;
use App\Models\Processor;
use App\Transfer\Import\AttributeRemapper;

it('remaps foreign keys to their destination counterparts and clears unmapped ones', function (): void {
    $organisation = Organisation::factory()->create();
    $target = Processor::factory()->for($organisation)->create();

    $remapper = new AttributeRemapper();
    $idMap = ['bundle-uuid' => $target];

    $entity = [
        'attributes' => [
            'name' => 'Verwerking',
            // maps to the destination model
            'processor_id' => 'bundle-uuid',
            // no entry in the id map -> cleared
            'system_id' => 'unknown-uuid',
            // parent links are restored separately -> always cleared here
            'parent_id' => 'some-parent',
            // non-string foreign key -> cleared
            'document_type_id' => 123,
            // already null -> untouched
            'receiver_id' => null,
            // not a foreign key -> untouched
            'description' => 'blijft',
        ],
    ];

    $attributes = $remapper->remap($entity, $idMap);

    expect($attributes['processor_id'])->toBe($target->id->toString())
        ->and($attributes['system_id'])->toBeNull()
        ->and($attributes['parent_id'])->toBeNull()
        ->and($attributes['document_type_id'])->toBeNull()
        ->and($attributes['receiver_id'])->toBeNull()
        ->and($attributes['name'])->toBe('Verwerking')
        ->and($attributes['description'])->toBe('blijft');
});

it('returns an empty attribute array when the entity has none', function (): void {
    $remapper = new AttributeRemapper();

    expect($remapper->attributes([]))->toBe([])
        ->and($remapper->attributes(['attributes' => ['a' => 'b']]))->toBe(['a' => 'b']);
});
