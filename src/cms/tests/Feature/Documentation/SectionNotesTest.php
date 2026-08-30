<?php

declare(strict_types=1);

use Filament\Schemas\Components\Grid;
use App\Documentation\FieldDescriber;
use App\Documentation\FormEnvironment;
use App\Documentation\SectionNotes;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\DocumentResource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Tests\Fixtures\Documentation\AwkwardNotesResource;

beforeEach(function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    $this->notes = new SectionNotes($this->environment, new FieldDescriber());
});

afterEach(function (): void {
    $this->environment->restore();
});

it('finds the first field in a flat list', function (): void {
    $name = $this->notes->firstFieldName([
        TextInput::make('eerste'),
        TextInput::make('tweede'),
    ]);

    expect($name)->toBe('eerste');
});

it('looks inside layout components for the first field', function (): void {
    $name = $this->notes->firstFieldName([
        Grid::make()->schema([TextInput::make('verstopt')]),
    ]);

    expect($name)->toBe('verstopt');
});

it('skips hidden fields and placeholders', function (): void {
    $name = $this->notes->firstFieldName([
        Hidden::make('verborgen'),
        Placeholder::make('tekstblok'),
        TextInput::make('echt'),
    ]);

    expect($name)->toBe('echt');
});

it('has no first field when there is none', function (): void {
    expect($this->notes->firstFieldName([Grid::make()]))->toBeNull();
});

it('ignores values that are not components', function (): void {
    expect($this->notes->firstFieldName(['tekst', 42, null]))->toBeNull();
});

it('reads the note that belongs to a section', function (): void {
    $this->notes->load(AvgResponsibleProcessingRecordResource::class);

    // The data-subject section carries a #[DocNote] on getStakeholder().
    $note = $this->notes->forSection([TextInput::make('stakeholders')]);

    expect($note)->toBe(__('documentation.avg_responsible_processing_record.stakeholders'));
});

it('has no note for a section without one', function (): void {
    $this->notes->load(AvgResponsibleProcessingRecordResource::class);

    expect($this->notes->forSection([TextInput::make('een_veld_zonder_notitie')]))->toBeNull();
});

it('has no note when the section has no fields', function (): void {
    $this->notes->load(AvgResponsibleProcessingRecordResource::class);

    expect($this->notes->forSection([Grid::make()]))->toBeNull();
});

it('works for a resource without schema classes', function (): void {
    // DocumentResource has no directory of its own with *Schemas.php.
    $this->notes->load(DocumentResource::class);

    expect($this->notes->forSection([TextInput::make('name')]))->toBeNull();
});

it('has no children for a component that cannot resolve them', function (): void {
    expect($this->notes->childrenOf(TextInput::make('a')))->toBe([]);
});

it('skips notes that cannot be resolved', function (): void {
    // Every method in this fixture carries a #[DocNote] that cannot land in the
    // document: no field to anchor it to, an argument it cannot supply, a
    // method that throws, a return value that is not a schema, or a translation
    // key that does not exist. None of them may derail the generator.
    $this->notes->load(AwkwardNotesResource::class);

    expect($this->notes->forSection([TextInput::make('untranslated')]))->toBeNull();
});
