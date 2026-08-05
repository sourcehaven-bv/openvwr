<?php

/**
 * The generator reads the real forms, so these tests check that the document
 * matches what the application says - not that a fixed text is reproduced. A new
 * field or a changed label must not break them; a broken generator must.
 */

declare(strict_types=1);

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->output = sys_get_temp_dir() . '/docs-datamodel-test-' . uniqid() . '.md';
    $this->prose = sys_get_temp_dir() . '/docs-prose-test-' . uniqid();
});

afterEach(function (): void {
    File::delete($this->output);
    File::deleteDirectory($this->prose);
});

it('generates the documentation', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect($this->output)->toBeReadableFile();

    $markdown = (string) file_get_contents($this->output);

    // The registers from the navigation group appear, each with its own heading.
    expect($markdown)
        ->toContain('# Verwerkingen AVG verantwoordelijke')
        ->toContain('# Verwerkingen AVG verwerker')
        ->toContain('# Verwerkingen WPG verantwoordelijke')
        ->toContain('# Algoritmes')
        ->toContain('# Datalekken');
});

it('writes a warning that the file is generated', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect((string) file_get_contents($this->output))
        ->toContain('Dit bestand wordt gegenereerd');
});

it('describes fields with their label, kind and help text', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    $markdown = (string) file_get_contents($this->output);

    expect($markdown)
        ->toContain('| Veld | Soort invoer | Toelichting |')
        // The label comes verbatim from the form.
        ->toContain(__('processing_record.name'))
        // And the helper text shown below it on screen.
        ->toContain('Geef een korte, herkenbare naam');
});

it('lists the options of a fixed choice list', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    // The legal bases show what the system can record; those belong in the
    // document.
    expect((string) file_get_contents($this->output))
        ->toContain('Keuze uit:')
        ->toContain('Toestemming betrokkene');
});

it('marks nested fields', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    // Fields inside a list (such as the data per data subject) are indented.
    expect((string) file_get_contents($this->output))->toContain('| » ');
});

it('includes the note from a DocNote attribute', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect((string) file_get_contents($this->output))
        ->toContain(__('documentation.avg_responsible_processing_record.stakeholders'));
});

it('inserts the handwritten chapters around the registers', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/10-intro.md', '# Inleiding');
    File::put($this->prose . '/90-slot.md', '# Slot');

    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    $markdown = (string) file_get_contents($this->output);

    $intro = strpos($markdown, '# Inleiding');
    $register = strpos($markdown, '# Verwerkingen AVG verantwoordelijke');
    $slot = strpos($markdown, '# Slot');

    expect($intro)->toBeLessThan($register);
    expect($register)->toBeLessThan($slot);
});

it('replaces the shared parts placeholder with a table', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/90-hergebruik.md', "# Hergebruik\n\n<!-- HERGEBRUIK-TABEL -->");

    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    $markdown = (string) file_get_contents($this->output);

    expect($markdown)
        ->not->toContain('<!-- HERGEBRUIK-TABEL -->')
        ->toContain('| Onderdeel | Hergebruikt over |')
        // Documents are attached to every register.
        ->toContain('| Documenten | Alle registers |')
        // Recipients are not: Wpg handles provision through its own articles.
        ->toContain('Ontvangers');
});

it('creates the output directory when it does not exist', function (): void {
    $nested = sys_get_temp_dir() . '/docs-datamodel-' . uniqid() . '/sub/doc.md';

    $this->artisan('docs:datamodel', [
        '--output' => $nested,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect($nested)->toBeReadableFile();

    File::deleteDirectory(dirname(dirname($nested)));
});

it('orders the registers the way the menu does', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    $markdown = (string) file_get_contents($this->output);

    // The order follows navigationSort, not a list inside the generator.
    expect(strpos($markdown, '# Verwerkingen AVG verantwoordelijke'))
        ->toBeLessThan(strpos($markdown, '# Verwerkingen AVG verwerker'));
    expect(strpos($markdown, '# Verwerkingen AVG verwerker'))
        ->toBeLessThan(strpos($markdown, '# Verwerkingen WPG verantwoordelijke'));
});

it('adds the register description from the translations', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect((string) file_get_contents($this->output))
        ->toContain(__('avg_responsible_processing_record.register_description'));
});

it('leaves the database connection as it found it', function (): void {
    $before = DB::getDefaultConnection();

    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect(DB::getDefaultConnection())->toBe($before);
});

it('generates the documentation in English', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
        '--locale' => 'en',
    ])->assertExitCode(0);

    $markdown = (string) file_get_contents($this->output);

    expect($markdown)
        ->toContain('| Field | Kind of input | Explanation |')
        ->toContain('This file is generated by')
        // No Dutch may leak through: an untranslated key would show up as the
        // key itself, a missing translation as the Dutch source text.
        ->not->toContain('Soort invoer')
        ->not->toContain('documentation.kind.');
});

it('leaves the application locale as it found it', function (): void {
    $before = App::currentLocale();

    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
        '--locale' => 'en',
    ])->assertExitCode(0);

    expect(App::currentLocale())->toBe($before);
});
