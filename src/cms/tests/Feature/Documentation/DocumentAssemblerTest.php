<?php

declare(strict_types=1);

use App\Documentation\DocumentAssembler;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Algorithm\AlgorithmRecord;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->assembler = new DocumentAssembler();
    $this->prose = sys_get_temp_dir() . '/docs-assembler-' . uniqid();
});

afterEach(function (): void {
    File::deleteDirectory($this->prose);
});

it('starts with a warning that the file is generated', function (): void {
    $markdown = $this->assembler->assemble(['# Register'], $this->prose, []);

    expect($markdown)->toStartWith('<!--');
    expect($markdown)->toContain('Wijzigingen hier gaan verloren');
});

it('works without any handwritten chapters', function (): void {
    $markdown = $this->assembler->assemble(['# Register'], $this->prose, []);

    expect($markdown)->toContain('# Register');
});

it('puts files before and after the registers by name', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/10-voor.md', '# Voor');
    File::put($this->prose . '/90-na.md', '# Na');

    $markdown = $this->assembler->assemble(['# Register'], $this->prose, []);

    expect(strpos($markdown, '# Voor'))->toBeLessThan(strpos($markdown, '# Register'));
    expect(strpos($markdown, '# Register'))->toBeLessThan(strpos($markdown, '# Na'));
});

it('skips an empty handwritten file', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/10-leeg.md', '   ');

    $markdown = $this->assembler->assemble(['# Register'], $this->prose, []);

    // Een leeg bestand mag geen losse scheidingslijn opleveren.
    expect($markdown)->not->toContain("---\n\n\n");
});

it('separates the parts with a horizontal rule', function (): void {
    $markdown = $this->assembler->assemble(['# Een', '# Twee'], $this->prose, []);

    expect($markdown)->toContain("# Een\n\n---\n\n# Twee");
});

it('builds the shared parts table from the models', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/90-hergebruik.md', '<!-- HERGEBRUIK-TABEL -->');

    $markdown = $this->assembler->assemble([], $this->prose, [
        'AVG verantwoordelijke' => AvgResponsibleProcessingRecord::class,
    ]);

    expect($markdown)
        ->toContain('| Onderdeel | Hergebruikt over |')
        ->toContain('Verwerkingsverantwoordelijken');
});

it('says "all registers" when every register shares a part', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/90-hergebruik.md', '<!-- HERGEBRUIK-TABEL -->');

    $markdown = $this->assembler->assemble([], $this->prose, [
        'AVG verantwoordelijke' => AvgResponsibleProcessingRecord::class,
        'Algoritmes' => AlgorithmRecord::class,
    ]);

    // Documenten hangen aan beide registers, verwerkers alleen aan de eerste.
    expect($markdown)
        ->toContain('| Documenten | Alle registers |')
        ->toContain('| Verwerkers | AVG verantwoordelijke |');
});

it('leaves out a part that no register uses', function (): void {
    File::makeDirectory($this->prose, 0o755, true);
    File::put($this->prose . '/90-hergebruik.md', '<!-- HERGEBRUIK-TABEL -->');

    $markdown = $this->assembler->assemble([], $this->prose, [
        'Algoritmes' => AlgorithmRecord::class,
    ]);

    // Een algoritme kent geen ontvangers; die regel hoort te ontbreken.
    expect($markdown)->not->toContain('| Ontvangers |');
});
