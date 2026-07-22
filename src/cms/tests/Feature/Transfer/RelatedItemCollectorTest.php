<?php

declare(strict_types=1);

use App\Models\Avg\AvgGoal;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\Processor;
use App\Transfer\Export\RelatedItemCollector;
use App\Transfer\TransferEntityType;

it('groups the related items of the given records per relation', function (): void {
    $organisation = Organisation::factory()->create();

    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create([
        'name' => 'Verwerking',
        'has_processors' => true,
        'has_systems' => true,
    ]);

    $processor = Processor::factory()->for($organisation)->create(['name' => 'Verwerker 1']);
    $goal = AvgGoal::factory()->for($organisation)->create(['goal' => 'Doel 1']);

    $record->processors()->attach($processor);
    $record->avgGoals()->attach($goal);

    $groups = (new RelatedItemCollector())->collect(collect([$record]));

    expect($groups)->toHaveKey('processors')
        ->and($groups)->toHaveKey('avgGoals')
        ->and($groups['processors']['type'])->toBe(TransferEntityType::PROCESSOR)
        ->and($groups['processors']['options'])->toBe([$processor->id->toString() => 'Verwerker 1'])
        ->and($groups['avgGoals']['type'])->toBe(TransferEntityType::AVG_GOAL)
        ->and($groups['avgGoals']['options'])->toBe([$goal->id->toString() => 'Doel 1'])
        // relations without any related items are skipped
        ->and($groups)->not->toHaveKey('tags');
});

it('returns no groups for records without related items', function (): void {
    $organisation = Organisation::factory()->create();
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();

    expect((new RelatedItemCollector())->collect(collect([$record])))->toBe([]);
});
