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
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\File;
use Tests\Fixtures\Documentation\BrokenRegisterResource;

beforeEach(function (): void {
    $this->output = sys_get_temp_dir() . '/docs-edge-' . uniqid() . '.md';
    $this->prose = sys_get_temp_dir() . '/docs-edge-prose-' . uniqid();

    $this->environment = new FormEnvironment();
    $this->environment->boot();

    $this->describer = new FieldDescriber();
    $this->notes = new SectionNotes($this->environment, $this->describer);
    $this->renderer = new RegisterRenderer($this->describer, $this->notes);
});

afterEach(function (): void {
    $this->environment->restore();
    File::delete($this->output);
});

it('skips a section without a heading', function (): void {
    $form = Schema::make($this->environment->makeFormHost())->components([
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
    $form = Schema::make($this->environment->makeFormHost())->components([
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
    $form = Schema::make($this->environment->makeFormHost())->components([
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

it('stops with an error when a register cannot be read', function (): void {
    // A resource whose form throws stands for a form that bails out halfway.
    // The command must report it and stop rather than leave half a document
    // behind.
    Filament::getPanel('admin')->resources([BrokenRegisterResource::class]);

    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])
        ->expectsOutputToContain('Failed:')
        ->assertExitCode(1);
});

it('reports a misconfigured panel instead of writing an empty document', function (): void {
    // An unknown panel id is the clearest way a panel can be misconfigured. The
    // command must say so rather than leave a document without content behind.
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
        '--panel' => 'a-panel-that-does-not-exist',
    ])->assertExitCode(1);

    expect(file_exists($this->output))->toBeFalse();
});
