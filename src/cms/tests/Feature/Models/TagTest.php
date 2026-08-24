<?php

declare(strict_types=1);

use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\ContactPerson;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\Dpia\DpiaPrescanRecord;
use App\Models\Dpia\DpiaRecord;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\Responsible;
use App\Models\System;
use App\Models\Tag;
use App\Models\Wpg\WpgProcessingRecord;

use function PHPUnit\Framework\assertCount;

// A tag is attachable to every entity that uses HasTags, through the polymorphic
// taggables table. Each relation is exercised here so the label overview - which
// lists them per entity type - keeps working when a model is added or renamed.
dataset('taggables', [
    'avg responsible processing record' => [AvgResponsibleProcessingRecord::class, 'avgResponsibleProcessingRecords'],
    'avg processor processing record' => [AvgProcessorProcessingRecord::class, 'avgProcessorProcessingRecords'],
    'wpg processing record' => [WpgProcessingRecord::class, 'wpgProcessingRecords'],
    'algorithm record' => [AlgorithmRecord::class, 'algorithmRecords'],
    'data breach record' => [DataBreachRecord::class, 'dataBreachRecords'],
    'dpia record' => [DpiaRecord::class, 'dpiaRecords'],
    'dpia prescan record' => [DpiaPrescanRecord::class, 'dpiaPrescanRecords'],
    'system' => [System::class, 'systems'],
    'responsible' => [Responsible::class, 'responsibles'],
    'processor' => [Processor::class, 'processors'],
    'receiver' => [Receiver::class, 'receivers'],
    'contact person' => [ContactPerson::class, 'contactPersons'],
    'document' => [Document::class, 'documents'],
]);

it('can be attached to a taggable', function (string $model, string $relation): void {
    $tag = Tag::factory()->create();
    $taggable = $model::factory()->create();

    $tag->{$relation}()->attach($taggable);

    assertCount(1, $tag->{$relation});
    expect($tag->{$relation}->first()->id)->toBe($taggable->id);
})->with('taggables');

it('can be attached to several taggables at once', function (): void {
    $tag = Tag::factory()->create();

    $record = AvgResponsibleProcessingRecord::factory()->create();
    $system = System::factory()->create();

    $tag->avgResponsibleProcessingRecords()->attach($record);
    $tag->systems()->attach($system);

    // The point of a shared label: one tag spans different entity types, so the
    // same afdeling or locatie can be found across the whole registration.
    assertCount(1, $tag->avgResponsibleProcessingRecords);
    assertCount(1, $tag->systems);
});
