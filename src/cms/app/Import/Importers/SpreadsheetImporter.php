<?php

declare(strict_types=1);

namespace App\Import\Importers;

use App\Components\Uuid\UuidInterface;
use App\Import\Factory;
use App\Import\Importer;
use App\Import\ImportFailedException;
use App\Import\Mapping\SheetReader;
use App\Jobs\ImportEntityJob;
use App\Jobs\ImportFinishedJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use function count;

/**
 * Imports rows from a spreadsheet (xlsx/csv) straight into a factory, using the
 * header row as the keys. Column headers may use dot-notation for nesting.
 *
 * This is the unattended route, used for a sheet whose columns already match
 * what a factory expects. For an arbitrary sheet, see the guided import in
 * App\Filament\Pages\ImportMapping.
 *
 * Snapshots and workflow states are out of scope here; see
 * docs/import_mapping_design.md.
 */
class SpreadsheetImporter implements Importer
{
    public function __construct(
        private readonly SheetReader $sheetReader,
    ) {
    }

    /**
     * @param class-string<Factory<Model>> $factoryClass
     *
     * @throws ImportFailedException
     */
    public function import(string $filename, string $input, string $factoryClass, UuidInterface $userId, string $organisationId): void
    {
        Log::info('starting input of spreadsheet-data', ['filename' => $filename]);

        $dataSets = $this->sheetReader->read($filename, $input)->rows;

        $dataSetCount = count($dataSets);
        if ($dataSetCount === 0) {
            Log::info('spreadsheet-dataset is empty, skipping import', ['factoryClass' => $factoryClass]);

            return;
        }

        Log::info('start dispatching jobs', ['dataSetCount' => $dataSetCount, 'factoryClass' => $factoryClass]);
        foreach ($dataSets as $dataSet) {
            ImportEntityJob::dispatch($factoryClass, $dataSet, $organisationId);
            Log::info('dispatched job to import data', ['factoryClass' => $factoryClass, 'organisationId' => $organisationId]);
        }

        Log::info('finished dispatching jobs', ['factoryClass' => $factoryClass]);
        ImportFinishedJob::dispatch($filename, $userId);
    }
}
