<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;

use function __;
use function expect;
use function fake;
use function it;

it('resets processor data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withProcessors()
        ->create([
            'has_processors' => true,
        ]);

    $avgProcessorProcessingRecord->has_processors = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->processors)
        ->toHaveCount(0);
});

it('resets processing goal data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withAvgGoals()
        ->create([
            'has_goal' => true,
        ]);

    $avgProcessorProcessingRecord->has_goal = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->avgGoals)
        ->toHaveCount(0);
});

it('resets passthrough country_other if not other on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->create([
            'outside_eu' => true,
            'country' => __('general.country_other'),
            'country_other' => fake()->word(),
        ]);

    $avgProcessorProcessingRecord->country = fake()->word();
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->country_other)
        ->toBeNull();
});

it('resets involved data data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withStakeholders()
        ->create([
            'has_involved' => true,
        ]);

    $avgProcessorProcessingRecord->has_involved = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->stakeholders)
        ->toHaveCount(0);
});

it('resets decision making data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withStakeholders()
        ->create([
            'decision_making' => true,
            'logic' => fake()->word(),
            'importance_consequences' => fake()->word(),
        ]);

    $avgProcessorProcessingRecord->decision_making = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->logic)->toBeEmpty()
        ->and($avgProcessorProcessingRecord->importance_consequences)->toBeEmpty();
});

it('resets system data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withSystems()
        ->create([
            'has_systems' => true,
        ]);

    $avgProcessorProcessingRecord->has_systems = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->systems)
        ->toHaveCount(0);
});

it('resets security data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->create([
            'has_security' => true,
            'measures_implemented' => true,
            'other_measures' => true,
            'measures_description' => fake()->word(),
            'has_pseudonymization' => true,
            'pseudonymization' => fake()->word(),
        ]);

    $avgProcessorProcessingRecord->has_security = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->measures_implemented)->toBeFalse()
        ->and($avgProcessorProcessingRecord->other_measures)->toBeFalse()
        ->and($avgProcessorProcessingRecord->measures_description)->toBeNull()
        ->and($avgProcessorProcessingRecord->has_pseudonymization)->toBeFalse()
        ->and($avgProcessorProcessingRecord->pseudonymization)->toBeEmpty();
});

it('resets passthrough data on save', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withStakeholders()
        ->create([
            'outside_eu' => true,
            'country' => fake()->word(),
            'outside_eu_protection_level' => true,
            'outside_eu_description' => fake()->word(),
            'outside_eu_protection_level_description' => fake()->word(),
        ]);

    $avgProcessorProcessingRecord->outside_eu = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->country)->toBeNull()
        ->and($avgProcessorProcessingRecord->outside_eu_protection_level)->toBeFalse()
        ->and($avgProcessorProcessingRecord->outside_eu_description)->toBeNull()
        ->and($avgProcessorProcessingRecord->outside_eu_protection_level_description)->toBeEmpty();
});

it('resets algorithm links on save but keeps the algorithms themselves', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->withAlgorithmRecords(2)
        ->create([
            'has_algorithms' => true,
        ]);

    $avgProcessorProcessingRecord->has_algorithms = false;
    $avgProcessorProcessingRecord->save();
    $avgProcessorProcessingRecord->refresh();

    expect($avgProcessorProcessingRecord->algorithmRecords)->toHaveCount(0)
        ->and(AlgorithmRecord::count())->toBe(2);
});
