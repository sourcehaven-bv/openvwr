<?php

/**
 * The edge cases of the documentation generator: what happens when a form or a
 * resource is not as expected. Those paths are rare, which is exactly why they
 * are worth pinning down - they decide whether a broken generator is noticed or
 * quietly produces half a document.
 */

declare(strict_types=1);

use App\Documentation\FieldDescriber;
use App\Documentation\FormEnvironment;
use App\Documentation\RegisterRenderer;
use App\Documentation\SectionNotes;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Filament\Resources\ContactPersonResource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;

beforeEach(function (): void {
    $this->environment = new FormEnvironment();
    $this->environment->boot();

    $this->describer = new FieldDescriber();
    $this->notes = new SectionNotes($this->environment, $this->describer);
    $this->renderer = new RegisterRenderer($this->describer, $this->notes);
});

afterEach(function (): void {
    $this->environment->restore();
});

it('skips a section without a heading', function (): void {
    $form = Form::make($this->environment->makeFormHost())->schema([
        // A section without a heading gets no chapter; its fields belong to the
        // surrounding structure.
        Section::make()->schema([TextInput::make('a')->label('Zonder kop')]),
        Section::make('Met kop')->schema([TextInput::make('b')->label('Veld')]),
    ]);

    $markdown = $this->renderer->render(
        $form,
        AvgResponsibleProcessingRecordResource::class,
        'Titel',
    );

    expect($markdown)
        ->toContain('## Met kop')
        ->not->toContain('Zonder kop');
});

it('leaves out a field without a label', function (): void {
    $form = Form::make($this->environment->makeFormHost())->schema([
        Section::make('Kop')->schema([
            TextInput::make('naamloos')->label(''),
            TextInput::make('b')->label('Wel een label'),
        ]),
    ]);

    $markdown = $this->renderer->render(
        $form,
        AvgResponsibleProcessingRecordResource::class,
        'Titel',
    );

    expect($markdown)
        ->toContain('Wel een label')
        ->not->toContain('naamloos');
});

it('has no register description when the translation is missing', function (): void {
    // ContactPersonResource has no register_description in the translations.
    $form = Form::make($this->environment->makeFormHost())->schema([
        Section::make('Kop')->schema([TextInput::make('a')->label('Veld')]),
    ]);

    $markdown = $this->renderer->render(
        $form,
        ContactPersonResource::class,
        'Contactpersonen',
    );

    // Only the title and the table, no stray introductory line before it.
    expect($markdown)->toStartWith("# Contactpersonen\n\n## Kop");
});
