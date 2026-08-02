<?php

declare(strict_types=1);

use App\Transfer\TransferBundleStorage;
use Illuminate\Support\Facades\Storage;
use Webmozart\Assert\InvalidArgumentException;

beforeEach(function (): void {
    Storage::fake('transfer');
    $this->bundleStorage = app(TransferBundleStorage::class);
});

it('writes a bundle built locally to the disk and reads it back', function (): void {
    $path = 'transfer/exports/bundle.zip';

    $this->bundleStorage->writeFromLocal($path, function (string $localPath): void {
        $zip = new ZipArchive();
        $zip->open($localPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('manifest.json', '{"format":"openvwr-transfer"}');
        $zip->close();
    });

    expect($this->bundleStorage->exists($path))->toBeTrue();

    // The round trip matters more than the bytes: ZipArchive needs a real local
    // path, which the disk itself cannot provide once it runs on object storage.
    $manifest = $this->bundleStorage->withLocalCopy($path, function (string $localPath): string {
        $zip = new ZipArchive();
        expect($zip->open($localPath, ZipArchive::RDONLY))->toBeTrue();
        $contents = (string) $zip->getFromName('manifest.json');
        $zip->close();

        return $contents;
    });

    expect($manifest)->toBe('{"format":"openvwr-transfer"}');
});

it('deletes a bundle', function (): void {
    $path = 'transfer/imports/bundle.zip';
    Storage::disk('transfer')->put($path, 'zip bytes');

    $this->bundleStorage->delete($path);

    expect($this->bundleStorage->exists($path))->toBeFalse();
});

it('copies a local file onto the disk', function (): void {
    $localPath = sys_get_temp_dir() . '/transfer-bundle-storage-test.zip';
    file_put_contents($localPath, 'zip bytes');

    try {
        $this->bundleStorage->putFile('transfer/imports/uploaded.zip', $localPath);
    } finally {
        unlink($localPath);
    }

    expect(Storage::disk('transfer')->get('transfer/imports/uploaded.zip'))->toBe('zip bytes');
});

it('removes the temporary file when building a bundle fails', function (): void {
    $capturedPath = null;

    try {
        $this->bundleStorage->writeFromLocal(
            'transfer/exports/never-written.zip',
            function (string $localPath) use (&$capturedPath): void {
                $capturedPath = $localPath;

                throw new RuntimeException('building the bundle failed');
            },
        );
    } catch (RuntimeException) {
        // expected: the point is what happens to the temporary file afterwards
    }

    expect($capturedPath)->toBeString()
        ->and(file_exists((string) $capturedPath))->toBeFalse()
        ->and($this->bundleStorage->exists('transfer/exports/never-written.zip'))->toBeFalse();
});

it('removes the temporary copy when reading a bundle fails', function (): void {
    $path = 'transfer/imports/bundle.zip';
    Storage::disk('transfer')->put($path, 'zip bytes');
    $capturedPath = null;

    try {
        $this->bundleStorage->withLocalCopy(
            $path,
            function (string $localPath) use (&$capturedPath): void {
                $capturedPath = $localPath;

                throw new RuntimeException('import failed');
            },
        );
    } catch (RuntimeException) {
        // expected
    }

    expect($capturedPath)->toBeString()
        ->and(file_exists((string) $capturedPath))->toBeFalse();
});

it('rejects a local file that cannot be opened', function (): void {
    expect(fn () => $this->bundleStorage->putFile('transfer/imports/x.zip', '/does/not/exist.zip'))
        ->toThrow(InvalidArgumentException::class);
});

it('rejects a bundle that is missing from the disk', function (): void {
    expect(fn () => $this->bundleStorage->withLocalCopy('transfer/imports/missing.zip', fn () => null))
        ->toThrow(InvalidArgumentException::class);
});
