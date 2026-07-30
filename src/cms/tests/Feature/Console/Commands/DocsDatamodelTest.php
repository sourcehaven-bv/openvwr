<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * De generator leest de echte formulieren uit, dus deze tests controleren of
 * het document klopt met wat er in de applicatie staat - niet of een vaste
 * tekst wordt gereproduceerd. Een nieuw veld of een gewijzigd label mag deze
 * tests niet laten falen; een kapotte generator wel.
 */
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

    // De registers uit de navigatiegroep komen erin, met hun eigen kop.
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
        // Het label komt letterlijk uit het formulier.
        ->toContain(__('processing_record.name'))
        // En de hulptekst die daaronder in het scherm staat.
        ->toContain('Geef een korte, herkenbare naam');
});

it('lists the options of a fixed choice list', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    // De rechtsgronden laten zien wat het systeem kan vastleggen; juist die
    // horen in het document te staan.
    expect((string) file_get_contents($this->output))
        ->toContain('Keuze uit:')
        ->toContain('Toestemming betrokkene');
});

it('marks nested fields', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    // Velden binnen een lijst (zoals de gegevens per betrokkene) worden
    // ingesprongen weergegeven.
    expect((string) file_get_contents($this->output))->toContain('| » ');
});

it('includes the note from a DocNote attribute', function (): void {
    $this->artisan('docs:datamodel', [
        '--output' => $this->output,
        '--prose' => $this->prose,
    ])->assertExitCode(0);

    expect((string) file_get_contents($this->output))
        ->toContain('Het meest gedetailleerde onderdeel van de registratie');
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
        // Documenten hangen aan elk register.
        ->toContain('| Documenten | Alle registers |')
        // Ontvangers niet: WPG regelt verstrekking via de Wpg-artikelen.
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

    // De volgorde volgt navigationSort, niet een lijst in de generator.
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
