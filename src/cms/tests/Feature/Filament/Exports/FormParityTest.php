<?php

declare(strict_types=1);

use App\Enums\Import\ImportTarget;
use App\Filament\Exports\AlgorithmRecordExporter;
use App\Filament\Exports\AvgProcessorProcessingRecordExporter;
use App\Filament\Exports\AvgResponsibleProcessingRecordExporter;
use App\Filament\Exports\DataBreachRecordExporter;
use App\Filament\Exports\WpgProcessingRecordExporter;
use App\Filament\Forms\FormHost;
use App\Import\Mapping\FieldSynonyms;
use App\Import\Mapping\FormFields;
use App\Import\Mapping\TargetOptions;
use Illuminate\Support\Facades\Config;

// Fields of the form that neither the export nor the import can carry: the
// documents (files), the sub-verwerkingen (the other side of the parent
// link) and the number the old json import gave a record.
const FORM_ONLY = ['import_number', 'document_id', 'children'];

// Fields of the form the export writes but a sheet may not set: the number
// OpenVWR assigns, the parent record, the team member who is the primary
// contact, and the publication date, which is a decision rather than data.
const EXPORTED_NOT_IMPORTED = ['entityNumber.number', 'parent_id', 'users', 'public_from'];

// Columns OpenVWR fills itself; the WPG register lists them as fillable.
const OWN_LIFE = ['created_at', 'updated_at'];

const REGISTERS = [
    'datalekken' => [DataBreachRecordExporter::class, ImportTarget::DataBreachRecord],
    'avg verantwoordelijke' => [AvgResponsibleProcessingRecordExporter::class, ImportTarget::AvgResponsibleProcessingRecord],
    'avg verwerker' => [AvgProcessorProcessingRecordExporter::class, ImportTarget::AvgProcessorProcessingRecord],
    'wpg' => [WpgProcessingRecordExporter::class, ImportTarget::WpgProcessingRecord],
    'algoritmes' => [AlgorithmRecordExporter::class, ImportTarget::AlgorithmRecord],
];

/**
 * The form is the one place that says what a register's fields are and what
 * they are called. Export and import are held to it here, so a column that
 * cannot be read back, or a field left out, fails before anyone uploads.
 */
it('exports every field of the form under the label the form uses', function (string $exporter, ImportTarget $target): void {
    $this->asFilamentUser();
    Config::set('features.wpg', true);

    /** @var FieldSynonyms $synonyms */
    $synonyms = $this->app->get(FieldSynonyms::class);

    $exported = [];
    foreach ($exporter::getColumns() as $column) {
        $exported[$synonyms->canonicalise($column->getLabel())] = true;
    }

    $missing = [];
    foreach (FormFields::for($target) as $name => $field) {
        if (in_array($name, FORM_ONLY, true)) {
            continue;
        }

        if (!array_key_exists($synonyms->canonicalise($field->label), $exported)) {
            $missing[$name] = $field->label;
        }
    }

    expect($missing)->toBe([]);
})->with(REGISTERS);

it('offers every field of the form to the import under the label the form uses', function (string $exporter, ImportTarget $target): void {
    $this->asFilamentUser();
    Config::set('features.wpg', true);

    /** @var FieldSynonyms $synonyms */
    $synonyms = $this->app->get(FieldSynonyms::class);

    $offered = [];
    foreach ((new TargetOptions($target))->flat() as $label) {
        $offered[$synonyms->canonicalise($label)] = true;
    }

    $missing = [];
    foreach (FormFields::for($target) as $name => $field) {
        if (in_array($name, FORM_ONLY, true) || in_array($name, EXPORTED_NOT_IMPORTED, true)) {
            continue;
        }

        if (!array_key_exists($synonyms->canonicalise($field->label), $offered)) {
            $missing[$name] = $field->label;
        }
    }

    expect($missing)->toBe([]);
})->with(REGISTERS);

it('exports no plain field the form does not have', function (string $exporter, ImportTarget $target): void {
    $this->asFilamentUser();
    Config::set('features.wpg', true);

    $modelClass = $target->modelClass();
    $fillable = (new $modelClass())->getFillable();

    $orphans = [];
    foreach ($exporter::getColumns() as $column) {
        $name = $column->getName();
        if (in_array($name, $fillable, true) && !in_array($name, OWN_LIFE, true) && !FormFields::has($target, $name)) {
            $orphans[$name] = $column->getLabel();
        }
    }

    expect($orphans)->toBe([]);
})->with(REGISTERS);

it('offers no plain field the form does not have', function (string $exporter, ImportTarget $target): void {
    $this->asFilamentUser();
    Config::set('features.wpg', true);

    $modelClass = $target->modelClass();
    $fillable = (new $modelClass())->getFillable();

    $orphans = [];
    foreach ((new TargetOptions($target))->flat() as $key => $label) {
        if ($key !== 'import_id' && in_array($key, $fillable, true) && !FormFields::has($target, $key)) {
            $orphans[$key] = $label;
        }
    }

    expect($orphans)->toBe([]);
})->with(REGISTERS);

it('reads a field behind a toggle and a link, and leaves a repeater closed', function (): void {
    $this->asFilamentUser();

    $fields = FormFields::for(ImportTarget::AvgResponsibleProcessingRecord);

    expect($fields)->toHaveKey('geb_dpia_automated')
        ->and($fields['receivers']->relation)->toBe('receivers')
        ->and($fields['avgGoals']->relation)->toBe('avgGoals')
        ->and($fields)->not->toHaveKey('goal')
        ->and($fields['name']->relation)->toBeNull()
        ->and(FormFields::relationLabel(ImportTarget::AvgProcessorProcessingRecord, 'processors'))->toBe('Subverwerkers')
        ->and(FormFields::relationLabel(ImportTarget::AvgProcessorProcessingRecord, 'dataBreachRecords'))->toBeNull()
        ->and(FormFields::label(ImportTarget::DataBreachRecord, 'nature_of_incident_other'))->toBe('Aard van incident — Namelijk')
        ->and(FormFields::label(ImportTarget::DataBreachRecord, 'no_such_field'))->toBeNull();
});

it('hosts a form without showing anything', function (): void {
    expect((new FormHost())->render())->toBe('');
});
