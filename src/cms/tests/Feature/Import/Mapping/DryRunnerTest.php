<?php

declare(strict_types=1);

use App\Enums\Import\MappingConfidence;
use App\Enums\Import\MappingTransform;
use App\Import\Mapping\DryRunner;
use App\Import\Mapping\MappingField;
use App\Import\Mapping\MappingProfile;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\DataBreachRecord;

function breachProfile(): MappingProfile
{
    return new MappingProfile(DataBreachRecord::class, [
        new MappingField('Naam', 'name', MappingTransform::Text, MappingConfidence::Exact),
        new MappingField('Type', 'type', MappingTransform::Text, MappingConfidence::Exact),
        new MappingField('Gemeld AP', 'ap_reported', MappingTransform::Boolean, MappingConfidence::Label),
        new MappingField('Gemeld FG', 'fg_reported', MappingTransform::Boolean, MappingConfidence::Label),
        new MappingField('Gemeld betrokkene', 'reported_to_involved', MappingTransform::Boolean, MappingConfidence::Label),
    ]);
}

/**
 * @param array<string, mixed> $overrides
 *
 * @return array<string, mixed>
 */
function completeRow(array $overrides = []): array
{
    $row = [
        'Naam' => 'Mail naar verkeerde ontvanger',
        'Type' => 'Definitief',
        'Gemeld AP' => 'ja',
        'Gemeld FG' => 'nee',
        'Gemeld betrokkene' => 'ja',
    ];

    return [...$row, ...$overrides];
}

it('accepts a complete row', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(breachProfile(), [completeRow()]);

    expect($result->fitCount())->toBe(1)
        ->and($result->issueCount())->toBe(0)
        ->and($result->hasIssues())->toBeFalse();
});

it('reports a row that misses a required field', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(breachProfile(), [completeRow(['Naam' => null])]);

    expect($result->fitCount())->toBe(0)
        ->and($result->issueCount())->toBe(1)
        ->and($result->issues[0]->reason)->toContain(__('data_breach_record.name'));
});

it('reports a value that cannot be converted, naming the source column', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(breachProfile(), [completeRow(['Gemeld AP' => 'misschien'])]);

    expect($result->issueCount())->toBe(1)
        ->and($result->issues[0]->reason)->toContain('Gemeld AP');
});

it('separates good rows from problem rows', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(breachProfile(), [
        completeRow(),
        completeRow(['Naam' => null]),
        completeRow(),
    ]);

    expect($result->fitCount())->toBe(2)
        ->and($result->issueCount())->toBe(1)
        ->and($result->issues[0]->rowNumber)->toBe(2);
});

it('keeps the original row with the issue so it can be shown', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(breachProfile(), [completeRow(['Naam' => null, 'Type' => 'Voorlopig'])]);

    expect($result->issues[0]->row['Type'])->toBe('Voorlopig');
});

it('writes nothing to the database', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $before = DataBreachRecord::query()->count();
    $dryRunner->run(breachProfile(), [completeRow(), completeRow()]);

    expect(DataBreachRecord::query()->count())->toBe($before);
});

/**
 * @return MappingProfile<AvgResponsibleProcessingRecord>
 */
function groupedProfile(): MappingProfile
{
    return new MappingProfile(AvgResponsibleProcessingRecord::class, [
        new MappingField('Id', 'import_id', MappingTransform::Text, MappingConfidence::Exact),
        new MappingField('Naam', 'name', MappingTransform::Text, MappingConfidence::Exact),
        new MappingField('Systeem', 'systems', MappingTransform::Text, MappingConfidence::Exact, relation: 'systems'),
    ], identity: 'Id');
}

it('folds the rows of one record and keeps one link entry per row', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(groupedProfile(), [
        ['Id' => '9717', 'Naam' => 'Salarisadministratie', 'Systeem' => 'Salarispakket'],
        ['Id' => '9717', 'Naam' => null, 'Systeem' => 'HR-systeem'],
        ['Id' => '9720', 'Naam' => 'Toegangsbeheer', 'Systeem' => null],
    ]);

    expect($result->fitCount())->toBe(2)
        ->and($result->issueCount())->toBe(0)
        ->and($result->fits[0]['number'])->toBe(1)
        ->and($result->fits[0]['attributes']['name'])->toBe('Salarisadministratie')
        ->and($result->fits[0]['row']['Systeem'])->toBe(['Salarispakket', 'HR-systeem'])
        ->and($result->fits[1]['number'])->toBe(3);
});

it('reports rows of one record that disagree on a plain field', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(groupedProfile(), [
        ['Id' => '9717', 'Naam' => 'Salarisadministratie', 'Systeem' => 'Salarispakket'],
        ['Id' => '9717', 'Naam' => 'Salaris', 'Systeem' => null],
    ]);

    expect($result->fitCount())->toBe(0)
        ->and($result->issues[0]->rowNumber)->toBe(1)
        ->and($result->issues[0]->reason)->toBe(__('import_mapping.issue.rows_disagree', ['rows' => '1, 2', 'column' => 'Naam']));
});

it('keeps a row without a key as a record of its own', function (): void {
    /** @var DryRunner $dryRunner */
    $dryRunner = $this->app->get(DryRunner::class);

    $result = $dryRunner->run(groupedProfile(), [
        ['Id' => null, 'Naam' => 'Eerste', 'Systeem' => null],
        ['Id' => null, 'Naam' => 'Tweede', 'Systeem' => null],
    ]);

    expect($result->fitCount())->toBe(2);
});
