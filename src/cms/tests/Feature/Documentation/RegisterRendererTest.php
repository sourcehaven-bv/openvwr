<?php

declare(strict_types=1);

use App\Documentation\FieldDescriber;
use App\Documentation\FormEnvironment;
use App\Documentation\RegisterRenderer;
use App\Documentation\SectionNotes;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

beforeEach(function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    $describer = new FieldDescriber();
    $this->notes = new SectionNotes($this->environment, $describer);
    $this->renderer = new RegisterRenderer($describer, $this->notes);

    // Builds a form from the given sections.
    $this->form = function (array $sections): Schema {
        return Schema::make($this->environment->makeFormHost())->components($sections);
    };
});

afterEach(function (): void {
    $this->environment->restore();
});

it('writes a chapter with a heading and a table', function (): void {
    $form = ($this->form)([
        Section::make('Security')->schema([
            TextInput::make('a')->label('Measure'),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'My register');

    expect($markdown)
        ->toContain('# My register')
        ->toContain('## Security')
        ->toContain('| Veld | Soort invoer | Toelichting |')
        ->toContain('| Measure | Tekst |');
});

it('adds the register description', function (): void {
    $form = ($this->form)([
        Section::make('Heading')->schema([TextInput::make('a')->label('Field')]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Title');

    expect($markdown)->toContain(__('avg_responsible_processing_record.register_description'));
});

it('marks fields inside a list', function (): void {
    $form = ($this->form)([
        Section::make('Purposes')->schema([
            Repeater::make('purposes')->label('Purposes')->schema([
                TextInput::make('purpose')->label('Purpose'),
            ]),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Title');

    expect($markdown)
        ->toContain('| Purposes | Lijst |')
        ->toContain('| » Purpose | Tekst |');
});

it('writes a subheading for a nested section', function (): void {
    $form = ($this->form)([
        Section::make('Data subjects')->schema([
            Section::make('Special data')
                ->description('Which special data?')
                ->schema([TextInput::make('a')->label('Gegeven')]),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Title');

    expect($markdown)->toContain('| **Special data** |  | Which special data? |');
});

it('leaves out hidden fields', function (): void {
    $form = ($this->form)([
        Section::make('Heading')->schema([
            Hidden::make('hidden_field'),
            TextInput::make('a')->label('Visible'),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Title');

    expect($markdown)
        ->toContain('Visible')
        ->not->toContain('hidden_field');
});

it('skips a section without fields', function (): void {
    $form = ($this->form)([
        Section::make('Empty')->schema([Hidden::make('hidden_field')]),
        Section::make('Filled')->schema([TextInput::make('a')->label('Field')]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Title');

    expect($markdown)
        ->not->toContain('## Empty')
        ->toContain('## Filled');
});

it('escapes a pipe so the table stays intact', function (): void {
    $form = ($this->form)([
        Section::make('Heading')->schema([
            TextInput::make('a')->label('One | with a pipe'),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Title');

    expect($markdown)->toContain('One \\| with a pipe');
});

it('fails when the form has no fields at all', function (): void {
    $form = ($this->form)([]);

    expect(fn () => $this->renderer->render(
        $form,
        AvgResponsibleProcessingRecordResource::class,
        'Title',
    ))->toThrow(RuntimeException::class, 'no fields found');
});

it('fails when it is handed something that is not a form', function (): void {
    expect(fn () => $this->renderer->render(
        'not a form',
        AvgResponsibleProcessingRecordResource::class,
        'Title',
    ))->toThrow(RuntimeException::class);
});
