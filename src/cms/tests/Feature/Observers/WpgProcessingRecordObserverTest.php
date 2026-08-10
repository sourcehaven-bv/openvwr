<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Wpg\WpgProcessingRecord;

use function expect;
use function fake;
use function it;

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
