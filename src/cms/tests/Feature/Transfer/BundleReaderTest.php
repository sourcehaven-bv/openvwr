<?php

declare(strict_types=1);

use App\Transfer\Import\BundleReader;
use App\Transfer\TransferException;
use Illuminate\Support\Facades\Config;

/**
 * @param array<string, string> $entries
 */
function writeTransferTestZip(array $entries): string
{
    $path = sprintf('%s/%s.zip', sys_get_temp_dir(), fake()->uuid());

    $zip = new ZipArchive();
    $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    foreach ($entries as $name => $contents) {
        $zip->addFromString($name, $contents);
    }
    $zip->close();

    return $path;
}

it('rejects a zip without a manifest', function (): void {
    $path = writeTransferTestZip(['something.json' => '{}']);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('manifest.json missing from zip');
    $bundleReader->read($path);
});

it('rejects a zip with an unsupported format', function (): void {
    $path = writeTransferTestZip(['manifest.json' => '{"format": "something-else", "version": 1}']);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('unsupported file format');
    $bundleReader->read($path);
});

it('rejects a zip with too many files', function (): void {
    Config::set('transfer.max_zipped_number_of_files', 1);

    $path = writeTransferTestZip([
        'manifest.json' => '{"format": "openvwr-transfer", "version": 1}',
        'entities/tag/extra.json' => '{}',
    ]);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('too many files in zip');
    $bundleReader->read($path);
});

it('rejects a zip with an invalid entity', function (): void {
    $path = writeTransferTestZip([
        'manifest.json' => '{"format": "openvwr-transfer", "version": 1}',
        'entities/tag/invalid.json' => '{"type": "nope", "id": "not-a-uuid"}',
    ]);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $bundleReader->read($path);
});

it('refuses media paths outside the media directory', function (): void {
    $path = writeTransferTestZip([
        'manifest.json' => '{"format": "openvwr-transfer", "version": 1}',
    ]);

    $bundleReader = app(BundleReader::class);

    expect($bundleReader->readMedia($path, '../../etc/passwd'))->toBeNull()
        ->and($bundleReader->readMedia($path, 'media/../secret'))->toBeNull();
});
