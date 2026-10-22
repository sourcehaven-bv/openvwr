<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Facades\AuditLog;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Processor;
use App\Models\System;
use App\Models\User;
use App\Models\Wpg\WpgProcessingRecord;
use App\Services\AuditLog\Register\LinksDetachedEvent;

use function collect;
use function count;
use function expect;
use function fake;
use function it;
use function strval;

it('resets processor data on save', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withProcessors()
        ->create([
            'has_processors' => true,
        ]);

    $wpgProcessingRecord->has_processors = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->processors)
        ->toHaveCount(0);
});

it('resets receiver data on save', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withProcessors()
        ->create([
            'article_17_a' => true,
        ]);

    $wpgProcessingRecord->article_17_a = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->explanation_transfer)
        ->toBeNull();
});

it('resets decision making data on save', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withProcessors()
        ->create([
            'decision_making' => true,
            'logic' => fake()->word(),
            'consequences' => fake()->word(),
        ]);

    $wpgProcessingRecord->decision_making = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->logic)->toBeNull()
        ->and($wpgProcessingRecord->consequences)->toBeNull();
});

it('resets systems data on save', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withSystems()
        ->create([
            'has_systems' => true,
        ]);

    $wpgProcessingRecord->has_systems = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->systems)
        ->toHaveCount(0);
});

it('resets security data on save', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->create([
            'has_security' => true,
            'measures_implemented' => true,
            'other_measures' => true,
            'measures_description' => fake()->word(),
            'has_pseudonymization' => true,
            'pseudonymization' => fake()->word(),
        ]);

    $wpgProcessingRecord->has_security = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->measures_implemented)->toBeFalse()
        ->and($wpgProcessingRecord->other_measures)->toBeFalse()
        ->and($wpgProcessingRecord->measures_description)->toBeNull()
        ->and($wpgProcessingRecord->has_pseudonymization)->toBeFalse()
        ->and($wpgProcessingRecord->pseudonymization)->toBeEmpty();
});

it('resets categories involved data on save', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withProcessors()
        ->create([
            'third_parties' => true,
            'third_party_explanation' => fake()->word(),
        ]);

    $wpgProcessingRecord->third_parties = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->third_party_explanation)->toBeNull();
});

it('resets algorithm links on save but keeps the algorithms themselves', function (): void {
    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withAlgorithmRecords(2)
        ->create([
            'has_algorithms' => true,
        ]);

    $wpgProcessingRecord->has_algorithms = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->algorithmRecords)->toHaveCount(0)
        ->and(AlgorithmRecord::count())->toBe(2);
});

it('keeps the linked processors and systems and audit logs the removed links', function (): void {
    AuditLog::spy();

    $user = User::factory()->create();
    $this->actingAs($user);

    $wpgProcessingRecord = WpgProcessingRecord::factory()
        ->withProcessors(2)
        ->withSystems(3)
        ->create([
            'has_processors' => true,
            'has_systems' => true,
        ]);
    $processorIds = $wpgProcessingRecord->processors()->pluck('processors.id')->map(strval(...))->sort()->values()->all();

    $wpgProcessingRecord->has_processors = false;
    $wpgProcessingRecord->has_systems = false;
    $wpgProcessingRecord->save();
    $wpgProcessingRecord->refresh();

    expect($wpgProcessingRecord->processors)->toHaveCount(0)
        ->and($wpgProcessingRecord->systems)->toHaveCount(0)
        ->and(Processor::count())->toBe(2)
        ->and(System::count())->toBe(3);

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof LinksDetachedEvent
            && $event->user?->id->equals($user->id) === true
            && $event->organisationId === $wpgProcessingRecord->organisation->id->toString()
            && $event->recordType === $wpgProcessingRecord->getMorphClass()
            && $event->recordId === $wpgProcessingRecord->id->toString()
            && $event->relation === 'processors'
            && collect($event->relatedIds)->sort()->values()->all() === $processorIds)
        ->once();
    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof LinksDetachedEvent
            && $event->relation === 'systems'
            && count($event->relatedIds) === 3)
        ->once();
});

it('does not audit log when there are no links to remove', function (): void {
    AuditLog::spy();

    $wpgProcessingRecord = WpgProcessingRecord::factory()->create([
        'has_processors' => false,
        'has_systems' => false,
        'has_algorithms' => false,
    ]);
    $wpgProcessingRecord->save();

    AuditLog::shouldNotHaveReceived('register');
});
