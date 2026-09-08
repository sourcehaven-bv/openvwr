<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Enums\Import\MappingTransform;
use App\Enums\Import\MissingEntityPolicy;
use App\Facades\Authentication;
use App\Filament\Exports\AlgorithmRecordExporter;
use App\Filament\Exports\AvgProcessorProcessingRecordExporter;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Exports\Exporter;
use App\Filament\Exports\WpgProcessingRecordExporter;
use App\Filament\Pages\ImportMapping;
use App\Import\Mapping\DryRunner;
use App\Import\Mapping\EditableMapping;
use App\Import\Mapping\EnumField;
use App\Import\Mapping\FieldOptions;
use App\Import\Mapping\FormDefaults;
use App\Import\Mapping\MappedRecordWriter;
use App\Import\Mapping\MappingAnalyser;
use App\Import\Mapping\MappingProfileRepository;
use App\Import\Mapping\SheetReader;
use App\Import\Mapping\TargetOptions;
use App\Import\Mapping\TransformResolver;
use App\Models\Casts\CalendarDateCast;
use App\Models\Processor;
use App\ValueObjects\CalendarDate;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

// Columns whose value belongs to a record's life in OpenVWR, not to its
// content, so a re-imported copy rightly differs there.
const OWN_LIFE_COLUMNS = ['number', 'entityNumber.number', 'created_at', 'updated_at', 'public_from'];

const FG_NOTE = 'De FG wil dit volgend jaar opnieuw zien.';

/**
 * Yes/no fields that open a section of the form; the observer clears the
 * section when they are off, so for a full record they are on.
 */
function opensSection(string $attribute): bool
{
    return Str::startsWith($attribute, ['has_', 'is_'])
        || in_array($attribute, [
            'decision_making',
            'outside_eu',
            'outside_eu_protection_level',
            'article_17_a',
            'cross_border',
            'affected_count_known',
            'ap_reported',
            'reported_to_involved',
            'fg_reported',
            'third_parties',
            'geb_dpia_executed',
        ], true);
}

/**
 * A value for a plain field that survives a trip through a sheet: within
 * the column's length, one of the choices where there are choices, dates on
 * the minute because the export writes minutes.
 */
function randomValue(Model $model, string $attribute): mixed
{
    $transform = app(TransformResolver::class)->forAttribute($model, $attribute);
    $options = app(FieldOptions::class)->for($model, $attribute);
    $limit = app(FormDefaults::class)->lengths($model::class)[$attribute] ?? 255;

    if (EnumField::enumClass($model, $attribute) !== null) {
        return EnumField::fromLabel($model, $attribute, fake()->randomElement($options));
    }

    $date = fake()->dateTimeBetween('-3 years', '+1 year')->setTime(fake()->numberBetween(0, 23), fake()->numberBetween(0, 59));

    return match ($transform) {
        MappingTransform::Boolean, MappingTransform::BooleanToDate => opensSection($attribute) || fake()->boolean(),
        MappingTransform::Date => ($model->getCasts()[$attribute] ?? null) === CalendarDateCast::class ? CalendarDate::instance(
            $date,
        ) : $date,
        MappingTransform::Integer => fake()->numberBetween(1, 5000),
        MappingTransform::StringList => $options === []
            ? fake()->words(3)
            : fake()->randomElements($options, min(3, count($options))),
        MappingTransform::Text => $options === []
            ? Str::limit(fake()->sentence(fake()->numberBetween(3, 12)), $limit - 3, '')
            : fake()->randomElement($options),
    };
}

/**
 * A record with every importable field filled and every kind of link
 * attached, so nothing the import offers is left untested.
 *
 * @template TModel of Model
 *
 * @param class-string<TModel> $modelClass
 *
 * @return TModel
 */
function fullRecord(ImportTarget $target): Model
{
    $organisationId = Authentication::organisation()->id;
    $modelClass = $target->modelClass();
    $record = $modelClass::factory()->create(['organisation_id' => $organisationId]);

    foreach (array_keys((new TargetOptions($target))->flat()) as $key) {
        if ($key === '' || $key === 'import_id' || !in_array($key, $record->getFillable(), true)) {
            continue;
        }

        $record->setAttribute($key, randomValue($record, $key));
    }

    foreach ($target->lookups() as $lookup) {
        $entry = $lookup->modelClass::factory()->create(
            ['organisation_id' => $organisationId, 'name' => 'Lijst ' . fake()->unique()->word()],
        );
        $record->setAttribute($lookup->foreignKey, $entry->getKey());
    }

    $record->save();

    foreach ($target->relations() as $relation) {
        // Registers that a row may only point at are made once and kept; the
        // rest is made in pairs, one with details and one without.
        if ($relation->missing === MissingEntityPolicy::Report) {
            $linked = $relation->modelClass::factory()->create(
                ['organisation_id' => $organisationId, $relation->nameAttribute => 'Register ' . fake()->unique()->word()],
            );
            $relation->relationFor($record)->attach($linked);

            continue;
        }

        foreach ([true, false] as $withDetails) {
            $entity = $relation->modelClass::factory()->create([
                'organisation_id' => $organisationId,
                $relation->nameAttribute => ucfirst(fake()->unique()->word()) . ' ' . fake()->numberBetween(1, 999),
            ]);

            foreach (array_keys($relation->extraAttributes) as $extra) {
                $entity->setAttribute($extra, $withDetails ? fake()->safeEmail() : '');
            }

            if ($withDetails && $entity instanceof Processor) {
                $entity->address()->create([
                    'address' => fake()->streetAddress(),
                    'postal_code' => fake()->postcode(),
                    'city' => fake()->city(),
                    'country' => 'Nederland',
                ]);
            }

            $entity->save();
            $relation->relationFor($record)->attach($entity);
        }
    }

    if (method_exists($record, 'remarks')) {
        $record->remarks()->create(['body' => 'Eerste notitie, met een komma.']);
        $record->remarks()->create(['body' => "Tweede notitie\nover twee regels."]);
    }

    if (method_exists($record, 'fgRemark')) {
        $record->fgRemark()->create(['body' => FG_NOTE]);
    }

    return $record->refresh();
}

/**
 * The export row of one record: column label => cell value.
 *
 * @param class-string<Exporter> $exporterClass
 *
 * @return array<string, mixed>
 */
function exportRow(string $exporterClass, Model $record): array
{
    $columnMap = [];
    foreach ($exporterClass::getColumns() as $column) {
        $columnMap[$column->getName()] = $column->getLabel();
    }

    $exporter = (new Export(['exporter' => $exporterClass]))->getExporter($columnMap, []);
    $values = $exporter($record->fresh());

    $row = [];
    foreach (array_keys($columnMap) as $index => $name) {
        $row[$name] = $values[$index];
    }

    return $row;
}

/**
 * @param array<string, mixed> $row keyed by column name
 */
function workbookOf(string $exporterClass, array $row): string
{
    $labels = [];
    foreach ($exporterClass::getColumns() as $column) {
        $labels[] = $column->getLabel();
    }

    $path = tempnam(sys_get_temp_dir(), 'export') . '.xlsx';
    $writer = new Writer();
    $writer->openToFile($path);
    $writer->addRow(Row::fromValues($labels));
    $writer->addRow(Row::fromValues(array_values($row)));
    $writer->close();

    $contents = (string) file_get_contents($path);
    unlink($path);

    return $contents;
}

function importAsProposed(ImportTarget $target, string $contents): ImportMapping
{
    $sheet = (new SheetReader())->read('export.xlsx', $contents);
    $profile = app(MappingAnalyser::class)->analyse($target, $sheet->headers, $sheet->rows);

    $page = new ImportMapping();
    $page->mount();
    $page->target = $target->value;
    $page->headers = $sheet->headers;
    $page->setRows($sheet->rows);
    $page->mapping = EditableMapping::fromProfile($sheet->headers, $profile);
    $page->step = ImportMapping::STEP_REVIEW;
    $page->apply(app(DryRunner::class), app(MappedRecordWriter::class), app(MappingProfileRepository::class));

    return $page;
}

it('exports a copy of a full record exactly as it exported the original', function (string $exporterClass, ImportTarget $target): void {
    $this->asFilamentUser();
    Config::set('features.wpg', true);
    $organisationId = Authentication::organisation()->id;

    $original = fullRecord($target);
    $before = exportRow($exporterClass, $original);
    $workbook = workbookOf($exporterClass, $before);

    // The FG's note stays with the FG: it is on the record, never in the sheet.
    expect(array_values($before))->not->toContain(FG_NOTE);

    // The record and everything the import may create go, so every detail
    // has to come back through the sheet.
    $name = $original->getAttribute('name');
    $original->delete();
    foreach ($target->relations() as $relation) {
        if ($relation->missing === MissingEntityPolicy::Create) {
            $relation->modelClass::query()->where('organisation_id', $organisationId)->get()->each->delete();
        }
    }

    $page = importAsProposed($target, $workbook);

    expect($page->result['imported'])->toBe(1)
        ->and($page->result['issues'])->toBe([])
        ->and($page->result['failures'])->toBe([]);

    $modelClass = $target->modelClass();
    $copy = $modelClass::query()->where('organisation_id', $organisationId)->where('name', $name)->firstOrFail();
    $after = exportRow($exporterClass, $copy);

    $differences = [];
    foreach ($before as $column => $value) {
        if (in_array($column, OWN_LIFE_COLUMNS, true)) {
            continue;
        }

        if ($after[$column] !== $value) {
            $differences[$column] = ['original' => $value, 'copy' => $after[$column]];
        }
    }

    expect($differences)->toBe([])
        ->and(count($before))->toBeGreaterThan(count(OWN_LIFE_COLUMNS));
})->with([
    'datalekken' => [DataBreachRecordExporter::class, ImportTarget::DataBreachRecord],
    'avg verantwoordelijke' => [AvgResponsibleProcessingRecordExporter::class, ImportTarget::AvgResponsibleProcessingRecord],
    'avg verwerker' => [AvgProcessorProcessingRecordExporter::class, ImportTarget::AvgProcessorProcessingRecord],
    'wpg' => [WpgProcessingRecordExporter::class, ImportTarget::WpgProcessingRecord],
    'algoritmes' => [AlgorithmRecordExporter::class, ImportTarget::AlgorithmRecord],
]);
