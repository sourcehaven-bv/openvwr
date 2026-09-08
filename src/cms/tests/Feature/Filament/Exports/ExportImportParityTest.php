<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Filament\Exports\AlgorithmRecordExporter;
use App\Filament\Exports\AvgProcessorProcessingRecordExporter;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Exports\WpgProcessingRecordExporter;
use App\Import\Mapping\FieldSynonyms;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\TargetOptions;
use Illuminate\Support\Facades\Config;

// Targets the export leaves out on purpose: the source reference is the
// register's own number, which the export writes under the number's own
// name (the analyser knows); the FG's note is for the FG alone and does not
// travel with the sheet.
const EXPORT_LEAVES_OUT = ['import_id', 'fgRemark'];

// The number comes back as the source reference, so it is not left over.
const READ_BACK_AS_REFERENCE = ['number', 'entityNumber.number'];

// Columns the export adds that are not a field a sheet can set: what OpenVWR
// assigns itself (number, dates, version), a link to something a sheet
// cannot name (the organisation, a team member, the parent record), or a
// decision a sheet may not take (the publication date).
const EXPORT_ONLY = [
    'organisation.name',
    'organisation.responsibleLegalEntity.name',
    'number',
    'entityNumber.number',
    'parent.entityNumber.number',
    'users.name',
    'documents_count',
    'created_at',
    'updated_at',
    'snapshot_latest_established',
    'snapshot_latest_status',
    'snapshot_latest_status_created_at',
    'public_from',
];

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

/**
 * The other way round: a column the export writes must be something the
 * import can take back, or a column OpenVWR fills itself. A column for a
 * field the form does not have reads back as "niet importeren" and looks
 * like a broken mapping.
 */
it(
    'exports no column the import cannot take back',
    function (string $exporter, ImportTarget $target): void {
        $this->asFilamentUser();
        Config::set('features.wpg', true);

        /** @var FieldSynonyms $synonyms */
        $synonyms = $this->app->get(FieldSynonyms::class);

        $offered = [];
        foreach ((new TargetOptions($target))->flat() as $label) {
            $offered[$synonyms->canonicalise($label)] = true;
        }

        $orphans = [];
        foreach ($exporter::getColumns() as $column) {
            if (in_array($column->getName(), EXPORT_ONLY, true)) {
                continue;
            }

            if (!array_key_exists($synonyms->canonicalise($column->getLabel()), $offered)) {
                $orphans[$column->getName()] = $column->getLabel();
            }
        }

        expect($orphans)->toBe([]);
    },
)->with([
    'datalekken' => [DataBreachRecordExporter::class, ImportTarget::DataBreachRecord],
    'avg verantwoordelijke' => [AvgResponsibleProcessingRecordExporter::class, ImportTarget::AvgResponsibleProcessingRecord],
    'avg verwerker' => [AvgProcessorProcessingRecordExporter::class, ImportTarget::AvgProcessorProcessingRecord],
    'wpg' => [WpgProcessingRecordExporter::class, ImportTarget::WpgProcessingRecord],
    'algoritmes' => [AlgorithmRecordExporter::class, ImportTarget::AlgorithmRecord],
]);

/**
 * The gate a user runs by hand: export, upload, look for "niet importeren".
 * Every heading the export writes must be placed by the analyser on its own,
 * apart from the columns OpenVWR fills itself; the number lands on the
 * source reference, so a second import of the sheet finds its records.
 */
it(
    'reads its own export back with nothing left over',
    function (string $exporter, ImportTarget $target): void {
        $this->asFilamentUser();
        Config::set('features.wpg', true);

        // A register without a source reference (algoritmes) keeps its
        // number to itself.
        $hasReference = (new TargetOptions($target))->allows('import_id');

        $headers = [];
        $ownLabels = [];
        foreach ($exporter::getColumns() as $column) {
            $headers[] = $column->getLabel();
            $readBack = $hasReference && in_array($column->getName(), READ_BACK_AS_REFERENCE, true);

            if (in_array($column->getName(), EXPORT_ONLY, true) && !$readBack) {
                $ownLabels[] = $column->getLabel();
            }
        }

        $profile = $this->app->get(MappingAnalyser::class)->analyse($target, $headers);
        $reference = array_filter($profile->fields, static fn ($field): bool => $field->target === 'import_id');

        expect($profile->unmapped)->toBe($ownLabels)
            ->and(array_values($reference))->toHaveCount($hasReference ? 1 : 0);
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
