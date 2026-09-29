<?php

declare(strict_types=1);

use App\Components\Uuid\Uuid;
use App\Import\Importers\SpreadsheetImporter;
use App\Jobs\ImportEntityJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

uses(TestCase::class);

/**
 * @param array<int, array<int, string>> $rows
 */
function xlsxContents(array $rows): string
{
    $path = sprintf('%s/%s.xlsx', sys_get_temp_dir(), Str::uuid()->toString());

    $writer = new XlsxWriter();
    $writer->openToFile($path);
    foreach ($rows as $row) {
        $writer->addRow(Row::fromValues($row));
    }
    $writer->close();

    $contents = file_get_contents($path);
    unlink($path);

    return $contents;
}

it('dispatches a job per data row', function (): void {
    Bus::fake();
    Log::spy();

    $contents = xlsxContents([
        ['Naam', 'Omschrijving'],
        ['Eerste', 'een'],
        ['Tweede', 'twee'],
    ]);

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatchedTimes(ImportEntityJob::class, 2);
});

it('maps header columns onto row values', function (): void {
    Bus::fake();
    Log::spy();

    $contents = xlsxContents([
        ['Naam', 'Omschrijving'],
        ['Eerste', 'een'],
    ]);

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatched(ImportEntityJob::class, function (ImportEntityJob $job): bool {
        return $job->data === ['Naam' => 'Eerste', 'Omschrijving' => 'een'];
    });
});

it('expands dot-notation headers into nested data', function (): void {
    Bus::fake();
    Log::spy();

    $contents = xlsxContents([
        ['Naam', 'Beveiliging.Encryptie'],
        ['Eerste', 'AES'],
    ]);

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatched(ImportEntityJob::class, function (ImportEntityJob $job): bool {
        return $job->data === [
            'Naam' => 'Eerste',
            'Beveiliging' => ['Encryptie' => 'AES'],
        ];
    });
});

it('skips rows without any value', function (): void {
    Bus::fake();
    Log::spy();

    $contents = xlsxContents([
        ['Naam', 'Omschrijving'],
        ['Eerste', 'een'],
        ['', ''],
        ['Tweede', 'twee'],
    ]);

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatchedTimes(ImportEntityJob::class, 2);
});

it('ignores columns without a header', function (): void {
    Bus::fake();
    Log::spy();

    $contents = xlsxContents([
        ['Naam', ''],
        ['Eerste', 'zwerfwaarde'],
    ]);

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatched(ImportEntityJob::class, function (ImportEntityJob $job): bool {
        return $job->data === ['Naam' => 'Eerste'];
    });
});

it('reads blank cells as null', function (): void {
    Bus::fake();
    Log::spy();

    $contents = xlsxContents([
        ['Naam', 'Omschrijving'],
        ['Eerste', '  '],
    ]);

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatched(ImportEntityJob::class, function (ImportEntityJob $job): bool {
        return $job->data === ['Naam' => 'Eerste', 'Omschrijving' => null];
    });
});

it('converts date cells to the configured import format', function (): void {
    Bus::fake();
    Log::spy();

    $path = sprintf('%s/%s.xlsx', sys_get_temp_dir(), Str::uuid()->toString());
    $writer = new XlsxWriter();
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues(['Naam', 'Datum melding']));
    $writer->addRow(new Row([
        Cell::fromValue('Eerste'),
        Cell::fromValue(new DateTimeImmutable('2026-03-04 10:30:00'), (new Style())->setFormat('yyyy-mm-dd')),
    ]));
    $writer->close();

    $contents = file_get_contents($path);
    unlink($path);

    $this->app->get(SpreadsheetImporter::class)->import(
        'meldingen.xlsx',
        $contents,
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatched(ImportEntityJob::class, function (ImportEntityJob $job): bool {
        return $job->data === ['Naam' => 'Eerste', 'Datum melding' => '2026-03-04T10:30:00'];
    });
});

it('imports csv as well', function (): void {
    Bus::fake();
    Log::spy();

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.csv',
        "Naam,Omschrijving\nEerste,een\n",
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertDispatched(ImportEntityJob::class, function (ImportEntityJob $job): bool {
        return $job->data === ['Naam' => 'Eerste', 'Omschrijving' => 'een'];
    });
});

it('does not dispatch jobs when there are no data rows', function (): void {
    Bus::fake();
    Log::spy();

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.csv',
        "Naam,Omschrijving\n",
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );

    Bus::assertNotDispatched(ImportEntityJob::class);
});

it('fails on an unsupported file type', function (): void {
    Log::spy();

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.ods',
        'whatever',
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );
})->expectExceptionMessage('xlsx- en csv-bestanden');

it('fails on an empty sheet', function (): void {
    Log::spy();

    $this->app->get(SpreadsheetImporter::class)->import(
        'verwerkingen.csv',
        '',
        fake()->word(),
        Uuid::fromString(fake()->uuid()),
        fake()->uuid(),
    );
})->expectExceptionMessage('geen kopregel');
