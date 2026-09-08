<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Enums\Import\MissingEntityPolicy;
use App\Import\Mapping\TargetOptions;
use App\Models\DataBreachRecord;
use App\Models\Organisation;
use Illuminate\Support\Facades\Config;

it('offers every register, each with the fields, links and lookups its model has', function (): void {
    Config::set('features.wpg', true);

    foreach (ImportTarget::cases() as $target) {
        $options = (new TargetOptions($target))->flat();

        expect($options)->toHaveKey('name')
            ->and($target->label())->not->toContain('.');
    }

    expect(ImportTarget::options())->toHaveCount(7);
});

it('derives links and shared entities from the model', function (): void {
    $keys = static fn (ImportTarget $target): array => array_map(
        static fn ($relation): string => $relation->key,
        $target->relations(),
    );

    expect($keys(ImportTarget::AvgProcessorProcessingRecord))->toContain(
        'processors',
        'systems',
        'receivers',
        'responsibles',
        'dataBreachRecords',
    )
        ->and($keys(ImportTarget::AlgorithmRecord))->toContain('avgResponsibleProcessingRecords')
        ->and($keys(ImportTarget::AlgorithmRecord))->not->toContain('processors')
        ->and($keys(ImportTarget::DpiaPrescanRecord))->toContain('dpiaRecords')
        ->and($keys(ImportTarget::DataBreachRecord))->toContain('responsibles', 'avgResponsibleProcessingRecords');
});

it('never creates a register record from a link, only reports it', function (): void {
    foreach (ImportTarget::AlgorithmRecord->relations() as $relation) {
        // Labels are the one shared entity every register has; they are made freely.
        if ($relation->key === 'tags') {
            continue;
        }

        expect($relation->missing)->toBe(MissingEntityPolicy::Report);
    }
});

it('offers the lookup lists of the algorithm register', function (): void {
    $options = (new TargetOptions(ImportTarget::AlgorithmRecord))->flat();

    expect($options)->toHaveKey('lookup:theme')
        ->and($options)->toHaveKey('lookup:status')
        ->and($options)->toHaveKey('lookup:publication_category')
        ->and($options)->not->toHaveKey('import_id');
});

it('hides the wpg register while its feature flag is off', function (): void {
    Config::set('features.wpg', false);

    expect(ImportTarget::options())->not->toHaveKey(ImportTarget::WpgProcessingRecord->value)
        ->and(ImportTarget::WpgProcessingRecord->enabled())->toBeFalse();

    $keys = array_map(static fn ($relation): string => $relation->key, ImportTarget::DataBreachRecord->relations());

    expect($keys)->not->toContain('wpgProcessingRecords');
});

it('finds the target for a model and refuses a model that is none', function (): void {
    expect(ImportTarget::forModel(DataBreachRecord::class))->toBe(ImportTarget::DataBreachRecord)
        ->and(fn () => ImportTarget::forModel(Organisation::class))->toThrow(InvalidArgumentException::class);
});

it('offers only fields that exist as columns, whatever the model calls fillable', function (): void {
    // "service" is fillable on the AVG record but has no column; the dienst is
    // a lookup list. Offering it made "Dienst" map onto it and the insert fail.
    $options = (new TargetOptions(ImportTarget::AvgResponsibleProcessingRecord))->flat();

    expect($options)->not->toHaveKey('service')
        ->and($options)->toHaveKey('lookup:service');
});
