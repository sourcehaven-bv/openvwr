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

it('reads a media entry from the zip and returns null when it is missing', function (): void {
    $path = writeTransferTestZip([
        'manifest.json' => '{"format": "openvwr-transfer", "version": 1}',
        'media/some-uuid/file.txt' => 'file-contents',
    ]);

    $bundleReader = app(BundleReader::class);

    expect($bundleReader->readMedia($path, 'media/some-uuid/file.txt'))->toBe('file-contents')
        ->and($bundleReader->readMedia($path, 'media/some-uuid/missing.txt'))->toBeNull();
});

it('rejects a zip that cannot be opened', function (): void {
    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('could not open zip file');
    $bundleReader->read(sprintf('%s/%s-does-not-exist.zip', sys_get_temp_dir(), fake()->uuid()));
});

it('rejects a zip with a file that is too large', function (): void {
    Config::set('transfer.max_zipped_filesize', 0);

    $path = writeTransferTestZip([
        'manifest.json' => '{"format": "openvwr-transfer", "version": 1}',
        'entities/tag/big.json' => '{"type":"tag"}',
    ]);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('filesize too large in zip');
    $bundleReader->read($path);
});

it('rejects a zip with an unsupported format version', function (): void {
    $path = writeTransferTestZip([
        'manifest.json' => '{"format": "openvwr-transfer", "version": 99}',
    ]);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('unsupported format version');
    $bundleReader->read($path);
});

it('rejects a zip with invalid json in the manifest', function (): void {
    $path = writeTransferTestZip([
        'manifest.json' => '{not valid json',
    ]);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('invalid json in zip');
    $bundleReader->read($path);
});

it('rejects a zip whose manifest json is not an object', function (): void {
    $path = writeTransferTestZip([
        'manifest.json' => '42',
    ]);

    $bundleReader = app(BundleReader::class);

    $this->expectException(TransferException::class);
    $this->expectExceptionMessage('invalid json structure in zip');
    $bundleReader->read($path);
});
