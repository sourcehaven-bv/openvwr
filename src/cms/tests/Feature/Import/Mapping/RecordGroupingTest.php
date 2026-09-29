<?php

declare(strict_types=1);

use App\Import\Mapping\RecordGrouping;

/**
 * The shape a register tool exports: the record's columns repeated on every
 * row, and each row carrying one item of one list.
 *
 * @return array<int, array<string, mixed>>
 */
function flattenedRows(): array
{
    $record = static fn (string $id, string $name, array $item): array => [
        'Id' => $id,
        'Naam' => $name,
        'Status' => 'Vastgesteld',
        'Dienst' => 'HR',
        'Tekst' => null,
        'Systeem' => null,
        'Doel' => null,
        ...$item,
    ];

    return [
        $record('9717', 'Salarisadministratie', ['Tekst' => 'Overgenomen uit het oude register']),
        $record('9717', 'Salarisadministratie', ['Doel' => 'Uitbetalen van salaris']),
        $record('9717', 'Salarisadministratie', ['Systeem' => 'Salarispakket']),
        $record('9717', 'Salarisadministratie', ['Systeem' => 'HR-systeem']),
        $record('9720', 'Toegangsbeheer', ['Systeem' => 'Toegangssysteem']),
        $record('9720', 'Toegangsbeheer', ['Doel' => 'Beveiligen van panden']),
    ];
}

function grouping(): RecordGrouping
{
    return app(RecordGrouping::class);
}

it('proposes the column whose repeated values mark the rows of one record', function (): void {
    $rows = flattenedRows();

    expect(grouping()->detect(array_keys($rows[0]), $rows))->toBe('Id');
});

it('prefers the id-like column when several columns mark the same rows', function (): void {
    // Naam and Status repeat exactly like Id does; Id is the one that reads as a key.
    $rows = flattenedRows();
    $headers = ['Status', 'Naam', 'Dienst', 'Id', 'Tekst', 'Systeem', 'Doel'];

    expect(grouping()->detect($headers, $rows))->toBe('Id');
});

it('proposes nothing when a repeated value belongs to different records', function (): void {
    // One row per data breach; "Type" repeats but the names differ.
    $rows = [
        ['Naam' => 'Eerste', 'Type' => 'Definitief', 'Samenvatting' => 'a'],
        ['Naam' => 'Tweede', 'Type' => 'Definitief', 'Samenvatting' => 'b'],
        ['Naam' => 'Derde', 'Type' => 'Voorlopig', 'Samenvatting' => 'c'],
    ];

    expect(grouping()->detect(array_keys($rows[0]), $rows))->toBeNull();
});

it('does not take a column that is blank on some row as the key', function (): void {
    $rows = flattenedRows();
    $rows[2]['Id'] = null;

    // Naam still marks the same rows, so it takes over.
    expect(grouping()->detect(array_keys($rows[0]), $rows))->toBe('Naam');
});

it('proposes nothing for a sheet of one row', function (): void {
    $rows = [flattenedRows()[0]];

    expect(grouping()->detect(array_keys($rows[0]), $rows))->toBeNull();
});

it('proposes nothing when the rows of a group disagree on a filled column', function (): void {
    $rows = flattenedRows();
    $rows[1]['Dienst'] = 'Zorg';

    expect(grouping()->detect(array_keys($rows[0]), $rows))->toBeNull();
});

it('reads a nested cell as one value when comparing', function (): void {
    $rows = flattenedRows();
    foreach (array_keys($rows) as $index) {
        $rows[$index]['Adres'] = ['Plaats' => 'Utrecht'];
    }

    expect(grouping()->detect(array_keys($rows[0]), $rows))->toBe('Id');
});
