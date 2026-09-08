<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Filament\Exports\AlgorithmRecordExporter;
use App\Filament\Exports\AvgProcessorProcessingRecordExporter;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Exports\WpgProcessingRecordExporter;
use App\Import\Mapping\FieldSynonyms;
use App\Import\Mapping\TargetOptions;
use Illuminate\Support\Facades\Config;

// Targets the export leaves out on purpose: the source reference is the
// register's own number, which the export writes under its own name.
const EXPORT_LEAVES_OUT = ['import_id'];

/**
 * Every field and link the import offers must come out of the export under a
 * heading the analyser recognises as exactly that target. Otherwise a sheet
 * that OpenVWR wrote cannot be read back without hand work.
 */
it(
    'exports every field and link the import offers, under the heading the import knows',
    function (string $exporter, ImportTarget $target): void {
        $this->asFilamentUser();
        Config::set('features.wpg', true);

        /** @var FieldSynonyms $synonyms */
        $synonyms = $this->app->get(FieldSynonyms::class);

        $exported = [];
        foreach ($exporter::getColumns() as $column) {
            $exported[$synonyms->canonicalise($column->getLabel())] = $column->getLabel();
        }

        $missing = [];
        foreach ((new TargetOptions($target))->flat() as $key => $label) {
            if ($key === '' || in_array($key, EXPORT_LEAVES_OUT, true)) {
                continue;
            }

            if (!array_key_exists($synonyms->canonicalise($label), $exported)) {
                $missing[$key] = $label;
            }
        }

        expect($missing)->toBe([]);
    },
)->with([
    'datalekken' => [DataBreachRecordExporter::class, ImportTarget::DataBreachRecord],
    'avg verantwoordelijke' => [AvgResponsibleProcessingRecordExporter::class, ImportTarget::AvgResponsibleProcessingRecord],
    'avg verwerker' => [AvgProcessorProcessingRecordExporter::class, ImportTarget::AvgProcessorProcessingRecord],
    'wpg' => [WpgProcessingRecordExporter::class, ImportTarget::WpgProcessingRecord],
    'algoritmes' => [AlgorithmRecordExporter::class, ImportTarget::AlgorithmRecord],
]);

it('never exports two columns under the same heading', function (string $exporter): void {
    $this->asFilamentUser();

    $labels = array_map(static fn ($column): string => $column->getLabel(), $exporter::getColumns());

    expect(array_diff_assoc($labels, array_unique($labels)))->toBe([]);
})->with([
    DataBreachRecordExporter::class,
    AvgResponsibleProcessingRecordExporter::class,
    AvgProcessorProcessingRecordExporter::class,
    WpgProcessingRecordExporter::class,
    AlgorithmRecordExporter::class,
]);
