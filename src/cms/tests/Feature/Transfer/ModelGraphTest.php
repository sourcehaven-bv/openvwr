<?php

declare(strict_types=1);

use App\Models\Organisation;
use App\Models\Processor;
use App\Transfer\ModelGraph;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

it('returns the uuid string for a model with a uuid key', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();

    expect(ModelGraph::id($processor))->toBe($processor->id->toString());
});

it('returns the string id for a model that does not cast its key to a uuid object', function (): void {
    $model = new class extends Model {
        protected $keyType = 'string';

        public $incrementing = false;
    };
    $model->setAttribute($model->getKeyName(), 'plain-string-id');

    expect(ModelGraph::id($model))->toBe('plain-string-id');
});

it('returns an empty list when the relation method is missing', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();

    expect(ModelGraph::related($processor, 'thisRelationDoesNotExist'))->toBe([]);
});

it('returns an empty list when the relation value is not an eloquent collection', function (): void {
    $organisation = Organisation::factory()->create();
    // 'organisation' is a BelongsTo, so the attribute resolves to a single model, not a collection
    $processor = Processor::factory()->for($organisation)->create();

    expect(ModelGraph::related($processor, 'organisation'))->toBe([]);
});

it('returns null for relatedOne when the relation method is missing', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();

    expect(ModelGraph::relatedOne($processor, 'thisRelationDoesNotExist'))->toBeNull();
});

it('returns null for relation when the relation method is missing', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();

    expect(ModelGraph::relation($processor, 'thisRelationDoesNotExist'))->toBeNull();
});

it('returns the relation instance for an existing relation', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();

    expect(ModelGraph::relation($processor, 'organisation'))->toBeInstanceOf(Relation::class);
});
