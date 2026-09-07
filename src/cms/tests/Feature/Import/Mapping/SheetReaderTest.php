<?php

declare(strict_types=1);

use App\Import\ImportFailedException;
use App\Import\Mapping\SheetReader;
use Illuminate\Support\Facades\Config;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

it('reads a semicolon-delimited csv, as dutch excel writes it', function (): void {
    $sheet = (new SheetReader())->read('export.csv', "Naam;Datum melding\nMail verkeerd;04-03-2026\n");

    expect($sheet->headers)->toBe(['Naam', 'Datum melding'])
        ->and($sheet->rows)->toBe([['Naam' => 'Mail verkeerd', 'Datum melding' => '04-03-2026']]);
});

it('reads a tab-delimited csv', function (): void {
    $sheet = (new SheetReader())->read('export.csv', "Naam\tType\nEen\tDefinitief\n");

    expect($sheet->headers)->toBe(['Naam', 'Type']);
});

it('still reads a comma-delimited csv whose values contain semicolons', function (): void {
    $sheet = (new SheetReader())->read('export.csv', "Naam,Type,Extra\n\"a; b\",Definitief,x\n");

    expect($sheet->headers)->toBe(['Naam', 'Type', 'Extra'])
        ->and($sheet->rows[0]['Naam'])->toBe('a; b');
});

it('refuses a sheet with two columns of the same name', function (): void {
    expect(fn () => (new SheetReader())->read('export.csv', "Naam,Naam\nEen,Twee\n"))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.duplicate_column', ['column' => 'Naam']));
});

it('refuses a column name that is also the prefix of a dotted column', function (): void {
    // Arr::undot() would silently drop the value of "Beveiliging".
    expect(fn () => (new SheetReader())->read('export.csv', "Beveiliging,Beveiliging.Encryptie\nja,nee\n"))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.column_conflict', [
            'column' => 'Beveiliging',
            'other' => 'Beveiliging.Encryptie',
        ]));
});

it('refuses a sheet with more rows than the guided import can hold', function (): void {
    Config::set('import.mapping.max_rows', 2);

    expect(fn () => (new SheetReader())->read('export.csv', "Naam\nEen\nTwee\nDrie\n"))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.too_many_rows', ['max' => 2]));
});

it('refuses a file type it cannot read before touching it', function (): void {
    expect(fn () => (new SheetReader())->read('export.ods', 'whatever'))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.unsupported_type'));
});

it('hides the library error behind a plain message when the file is corrupt', function (): void {
    expect(fn () => (new SheetReader())->read('export.xlsx', 'this is not a zip'))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.unreadable'));
});

it('skips rows without any value', function (): void {
    $sheet = (new SheetReader())->read('export.csv', "Naam,Type\nEen,Definitief\n,\n  ,\nTwee,Voorlopig\n");

    expect($sheet->rows)->toHaveCount(2);
});

it('refuses a header row without any names', function (): void {
    expect(fn () => (new SheetReader())->read('export.csv', ",,\nEen,Twee,Drie\n"))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.no_header'));
});

it('keeps typed cells from a workbook as they are', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'sheet') . '.xlsx';
    $writer = new Writer();
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues(['Naam', 'Aantal']));
    $writer->addRow(Row::fromValues(['Een', 42]));
    $writer->close();

    $sheet = (new SheetReader())->read('export.xlsx', (string) file_get_contents($path));
    unlink($path);

    expect($sheet->rows[0]['Aantal'])->toEqual(42);
});
