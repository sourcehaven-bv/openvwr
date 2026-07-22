<?php

declare(strict_types=1);

use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Transfer\Import\RelationRestorer;
use App\Transfer\Import\TransferBundle;

it('restores a self-referencing parent link', function (): void {
    $organisation = Organisation::factory()->create();
    $parent = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Ouder']);
    $child = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Kind']);

    $bundle = new TransferBundle([], [
        'child-uuid' => [
            'type' => 'avg_responsible_processing_record',
            'attributes' => ['parent_id' => 'parent-uuid'],
        ],
    ]);

    $idMap = [
        'parent-uuid' => $parent,
        'child-uuid' => $child,
    ];

    (new RelationRestorer())->restore($bundle, $idMap, ['child-uuid' => true]);

    expect($child->refresh()->getAttribute('parent_id')?->toString())->toBe($parent->id->toString());
});

it('ignores entities that were not written', function (): void {
    $organisation = Organisation::factory()->create();
    $child = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Kind']);

    $bundle = new TransferBundle([], [
        'child-uuid' => [
            'type' => 'avg_responsible_processing_record',
            'attributes' => ['parent_id' => 'parent-uuid'],
        ],
    ]);

    (new RelationRestorer())->restore($bundle, ['child-uuid' => $child], []);

    expect($child->refresh()->getAttribute('parent_id'))->toBeNull();
});

it('ignores relations that are not an array', function (): void {
    $organisation = Organisation::factory()->create();
    $model = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $bundle = new TransferBundle([], [
        'uuid' => [
            'type' => 'avg_responsible_processing_record',
            'relations' => 'not-an-array',
            'attributes' => [],
        ],
    ]);

    (new RelationRestorer())->restore($bundle, ['uuid' => $model], ['uuid' => true]);

    expect($model->refresh()->processors()->count())->toBe(0);
});

it('skips relation entries with a non-string name, non-array ids or a non pivot relation', function (): void {
    $organisation = Organisation::factory()->create();
    $model = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $bundle = new TransferBundle([], [
        'uuid' => [
            'type' => 'avg_responsible_processing_record',
            'relations' => [
                // non-array related ids -> skipped
                'processors' => 'nope',
                // 'parent' resolves to a BelongsTo, not a BelongsToMany -> skipped
                'parent' => ['x'],
            ],
            'attributes' => [],
        ],
    ]);

    (new RelationRestorer())->restore($bundle, ['uuid' => $model], ['uuid' => true]);

    expect($model->refresh()->processors()->count())->toBe(0);
});

it('does not restore a parent link that points outside the imported set', function (): void {
    $organisation = Organisation::factory()->create();
    $model = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    $bundle = new TransferBundle([], [
        'uuid' => [
            'type' => 'avg_responsible_processing_record',
            'attributes' => ['parent_id' => 'unknown-uuid'],
        ],
    ]);

    (new RelationRestorer())->restore($bundle, ['uuid' => $model], ['uuid' => true]);

    expect($model->refresh()->getAttribute('parent_id'))->toBeNull();
});
