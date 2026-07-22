<?php

declare(strict_types=1);

use App\Models\Avg\AvgGoal;
use App\Models\Organisation;
use App\Models\Processor;
use App\Models\Receiver;
use App\Models\System;
use App\Models\User;
use App\Transfer\TransferEntityType;
use App\Transfer\TransferException;

it('resolves a type from a transferable model', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();

    expect(TransferEntityType::fromModel($processor))->toBe(TransferEntityType::PROCESSOR);
});

it('throws for a model that is not transferable', function (): void {
    $user = User::factory()->create();

    expect(fn () => TransferEntityType::fromModel($user))
        ->toThrow(TransferException::class, 'is not transferable');
});

it('resolves a type from a known model class', function (): void {
    expect(TransferEntityType::tryFromModelClass(Processor::class))->toBe(TransferEntityType::PROCESSOR);
});

it('returns null when the model class is unknown', function (): void {
    expect(TransferEntityType::tryFromModelClass(User::class))->toBeNull();
});

it('resolves a type from a class-basename key', function (): void {
    expect(TransferEntityType::fromKey(Processor::class))->toBe(TransferEntityType::PROCESSOR)
        ->and(TransferEntityType::fromKey(AvgGoal::class))->toBe(TransferEntityType::AVG_GOAL);
});

it('classifies main records, lookups and owned entities', function (): void {
    expect(TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD->isMainRecord())->toBeTrue()
        ->and(TransferEntityType::PROCESSOR->isMainRecord())->toBeFalse()
        ->and(TransferEntityType::DOCUMENT_TYPE->isLookup())->toBeTrue()
        ->and(TransferEntityType::PROCESSOR->isLookup())->toBeFalse()
        ->and(TransferEntityType::ADDRESS->isOwned())->toBeTrue()
        ->and(TransferEntityType::PROCESSOR->isOwned())->toBeFalse();
});

it('exposes the match column per type', function (): void {
    expect(TransferEntityType::AVG_GOAL->matchColumn())->toBe('goal')
        ->and(TransferEntityType::SYSTEM->matchColumn())->toBe('description')
        ->and(TransferEntityType::RECEIVER->matchColumn())->toBe('description')
        ->and(TransferEntityType::ADDRESS->matchColumn())->toBeNull()
        ->and(TransferEntityType::REMARK->matchColumn())->toBeNull()
        ->and(TransferEntityType::PROCESSOR->matchColumn())->toBe('name');
});

it('uses the match column value as display name', function (): void {
    $organisation = Organisation::factory()->create();
    $system = System::factory()->for($organisation)->create(['description' => 'Systeem A']);

    expect(TransferEntityType::SYSTEM->displayName($system))->toBe('Systeem A');
});

it('falls back to the id as display name when the match column is empty', function (): void {
    $organisation = Organisation::factory()->create();
    // Receiver matches on description; leave it empty to hit the id fallback
    $receiver = Receiver::factory()->for($organisation)->create(['description' => '']);

    expect(TransferEntityType::RECEIVER->displayName($receiver))->toBe($receiver->id->toString());
});

it('falls back to the id as display name when the type has no match column', function (): void {
    $organisation = Organisation::factory()->create();
    $processor = Processor::factory()->for($organisation)->create();
    $address = $processor->address()->create(['city' => 'Amsterdam']);

    expect(TransferEntityType::ADDRESS->displayName($address))->toBe($address->id->toString());
});

it('provides a translated label', function (): void {
    expect(TransferEntityType::PROCESSOR->label())->toBe(__('processor.model_singular'));
});
