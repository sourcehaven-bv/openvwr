<?php

declare(strict_types=1);

use App\Documentation\FieldDescriber;
use App\Documentation\FormEnvironment;
use App\Documentation\RegisterRenderer;
use App\Documentation\SectionNotes;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

beforeEach(function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    $describer = new FieldDescriber();
    $this->notes = new SectionNotes($this->environment, $describer);
    $this->renderer = new RegisterRenderer($describer, $this->notes);

    // Bouwt een formulier met de meegegeven secties.
    $this->form = function (array $sections): Form {
        return Form::make($this->environment->makeFormHost())->schema($sections);
    };
});

afterEach(function (): void {
    $this->environment->restore();
});

it('writes a chapter with a heading and a table', function (): void {
    $form = ($this->form)([
        Section::make('Beveiliging')->schema([
            TextInput::make('a')->label('Maatregel'),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Mijn register');

    expect($markdown)
        ->toContain('# Mijn register')
        ->toContain('## Beveiliging')
        ->toContain('| Veld | Soort invoer | Toelichting |')
        ->toContain('| Maatregel | Tekst |');
});

it('adds the register description', function (): void {
    $form = ($this->form)([
        Section::make('Kop')->schema([TextInput::make('a')->label('Veld')]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Titel');

    expect($markdown)->toContain(__('avg_responsible_processing_record.register_description'));
});

it('marks fields inside a list', function (): void {
    $form = ($this->form)([
        Section::make('Doelen')->schema([
            Repeater::make('doelen')->label('Doelen')->schema([
                TextInput::make('doel')->label('Doel'),
            ]),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Titel');

    expect($markdown)
        ->toContain('| Doelen | Lijst |')
        ->toContain('| » Doel | Tekst |');
});

it('writes a subheading for a nested section', function (): void {
    $form = ($this->form)([
        Section::make('Betrokkenen')->schema([
            Section::make('Bijzondere gegevens')
                ->description('Welke bijzondere gegevens?')
                ->schema([TextInput::make('a')->label('Gegeven')]),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Titel');

    expect($markdown)->toContain('| **Bijzondere gegevens** |  | Welke bijzondere gegevens? |');
});

it('leaves out hidden fields', function (): void {
    $form = ($this->form)([
        Section::make('Kop')->schema([
            Hidden::make('verborgen'),
            TextInput::make('a')->label('Zichtbaar'),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Titel');

    expect($markdown)
        ->toContain('Zichtbaar')
        ->not->toContain('verborgen');
});

it('skips a section without fields', function (): void {
    $form = ($this->form)([
        Section::make('Leeg')->schema([Hidden::make('verborgen')]),
        Section::make('Gevuld')->schema([TextInput::make('a')->label('Veld')]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Titel');

    expect($markdown)
        ->not->toContain('## Leeg')
        ->toContain('## Gevuld');
});

it('escapes a pipe so the table stays intact', function (): void {
    $form = ($this->form)([
        Section::make('Kop')->schema([
            TextInput::make('a')->label('Een | met een pipe'),
        ]),
    ]);

    $markdown = $this->renderer->render($form, AvgResponsibleProcessingRecordResource::class, 'Titel');

    expect($markdown)->toContain('Een \\| met een pipe');
});

it('fails when the form has no fields at all', function (): void {
    $form = ($this->form)([]);

    expect(fn () => $this->renderer->render(
        $form,
        AvgResponsibleProcessingRecordResource::class,
        'Titel',
    ))->toThrow(RuntimeException::class, 'geen velden gevonden');
});

it('fails when it is handed something that is not a form', function (): void {
    expect(fn () => $this->renderer->render(
        'geen formulier',
        AvgResponsibleProcessingRecordResource::class,
        'Titel',
    ))->toThrow(RuntimeException::class);
});
