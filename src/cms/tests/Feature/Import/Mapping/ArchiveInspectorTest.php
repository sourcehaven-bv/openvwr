<?php

declare(strict_types=1);

use App\Import\ImportFailedException;
use App\Import\Mapping\ArchiveInspector;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * @param array<string, string> $entries
 */
function archiveWith(array $entries): string
{
    $path = sprintf('%s/%s.zip', sys_get_temp_dir(), Str::uuid()->toString());
    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE);
    foreach ($entries as $name => $contents) {
        $zip->addFromString($name, $contents);
    }
    $zip->close();

    $contents = (string) file_get_contents($path);
    unlink($path);

    return $contents;
}

it('counts the records per register and ignores what it does not know', function (): void {
    /** @var ArchiveInspector $inspector */
    $inspector = $this->app->get(ArchiveInspector::class);

    $found = $inspector->inspect(archiveWith([
        'Datalekken/a.json' => json_encode([['Naam' => 'Een'], ['Naam' => 'Twee']]),
        'Datalekken/readme.txt' => 'hello',
        'Onbekend/x.json' => json_encode([['Naam' => 'Drie']]),
    ]));

    // The text file counts as one entry so the total stays honest.
    expect($found)->toBe(['Datalekken' => 3]);
});

it('refuses something that is not a zip', function (): void {
    /** @var ArchiveInspector $inspector */
    $inspector = $this->app->get(ArchiveInspector::class);

    expect(fn () => $inspector->inspect('not a zip'))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.unreadable_archive'));
});

it('applies the same file-count limit as the importer', function (): void {
    Config::set('import.max_number_of_files_in_zip', 1);

    /** @var ArchiveInspector $inspector */
    $inspector = $this->app->get(ArchiveInspector::class);

    expect(fn () => $inspector->inspect(archiveWith(['a.txt' => 'a', 'b.txt' => 'b'])))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.archive_too_many_files'));
});

it('applies the same entry-size limit as the importer', function (): void {
    Config::set('import.max_zipped_file_filesize_in_mb', 0);

    /** @var ArchiveInspector $inspector */
    $inspector = $this->app->get(ArchiveInspector::class);

    expect(fn () => $inspector->inspect(archiveWith(['Datalekken/a.json' => '[{}]'])))
        ->toThrow(ImportFailedException::class, __('import_mapping.error.archive_entry_too_large'));
});
