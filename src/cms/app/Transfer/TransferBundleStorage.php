<?php

declare(strict_types=1);

namespace App\Transfer;

use Closure;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function fclose;
use function file_exists;
use function fopen;
use function stream_copy_to_stream;
use function sys_get_temp_dir;
use function unlink;

/**
 * Reads and writes transfer bundles on the configured transfer disk.
 *
 * Bundles are zip archives, and ZipArchive can only work against a real local
 * path. That is fine on a local disk but meaningless on s3, where Filesystem::path()
 * returns a bare object key rather than a file. Everything therefore goes through a
 * temporary local file, which is streamed to or from the disk. The disk itself only
 * ever sees stream reads and writes, so local and s3 behave identically.
 */
class TransferBundleStorage
{
    public const string DISK = 'transfer';

    public function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    public function exists(string $path): bool
    {
        return $this->disk()->exists($path);
    }

    public function delete(string $path): void
    {
        $this->disk()->delete($path);
    }

    /**
     * Copy a local file onto the transfer disk, streaming rather than reading it
     * into memory: bundles carry every attachment of the exported records.
     */
    public function putFile(string $path, string $localPath): void
    {
        // Checked up front so an unreadable file fails as an assertion rather
        // than as a warning from fopen.
        Assert::readable($localPath, 'Could not open file for upload: %s');

        $stream = fopen($localPath, 'rb');
        Assert::resource($stream, null, 'Could not open file for upload.');

        $this->disk()->writeStream($path, $stream);
    }

    /**
     * Run $callback against a local copy of a bundle stored on the disk.
     *
     * @template T
     *
     * @param Closure(string): T $callback receives the local path of the copy
     *
     * @return T
     */
    public function withLocalCopy(string $path, Closure $callback): mixed
    {
        $stream = $this->disk()->readStream($path);
        Assert::resource($stream, null, 'Could not read bundle from the transfer disk.');

        $tempPath = $this->tempPath();

        $target = fopen($tempPath, 'wb');
        Assert::resource($target, null, 'Could not open a temporary file for the bundle.');

        try {
            stream_copy_to_stream($stream, $target);
            fclose($target);
            fclose($stream);

            return $callback($tempPath);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    /**
     * Build a bundle in a temporary local file and store it at $path when done.
     *
     * @param Closure(string): void $callback receives the local path to write to
     */
    public function writeFromLocal(string $path, Closure $callback): void
    {
        $tempPath = $this->tempPath();

        try {
            $callback($tempPath);
            $this->putFile($path, $tempPath);
        } finally {
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    private function tempPath(): string
    {
        return sys_get_temp_dir() . '/openvwr-transfer-' . Str::uuid()->toString() . '.zip';
    }
}
