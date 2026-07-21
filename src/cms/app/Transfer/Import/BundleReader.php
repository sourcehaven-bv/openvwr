<?php

declare(strict_types=1);

namespace App\Transfer\Import;

use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferEntityType;
use App\Transfer\TransferException;
use Illuminate\Support\Str;
use JsonException;
use Webmozart\Assert\Assert;
use ZipArchive;

use function array_keys;
use function config;
use function is_array;
use function is_string;
use function json_decode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

class BundleReader
{
    private const int BYTES_IN_A_MEGABYTE = 1_024 * 1_024;

    /**
     * @throws TransferException
     */
    public function read(string $zipPath): TransferBundle
    {
        $zip = $this->open($zipPath);

        try {
            $manifest = $this->readManifest($zip);
            $entities = $this->readEntities($zip);
        } finally {
            $zip->close();
        }

        return new TransferBundle($manifest, $entities);
    }

    /**
     * @throws TransferException
     */
    public function readMedia(string $zipPath, string $entryPath): ?string
    {
        if (!Str::startsWith($entryPath, 'media/') || Str::contains($entryPath, '..')) {
            return null;
        }

        $zip = $this->open($zipPath);

        try {
            $contents = $zip->getFromName($entryPath);
        } finally {
            $zip->close();
        }

        return $contents === false ? null : $contents;
    }

    /**
     * @throws TransferException
     */
    private function open(string $zipPath): ZipArchive
    {
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::RDONLY) !== true) {
            throw new TransferException('could not open zip file');
        }

        $maxNumberOfFiles = config('transfer.max_zipped_number_of_files');
        Assert::integer($maxNumberOfFiles);

        if ($zip->count() > $maxNumberOfFiles) {
            throw new TransferException('too many files in zip');
        }

        $maxFilesize = config('transfer.max_zipped_filesize');
        Assert::integer($maxFilesize);

        for ($i = 0; $i < $zip->count(); $i++) {
            $statIndex = $zip->statIndex($i);
            Assert::isArray($statIndex);

            if ($statIndex['size'] > $maxFilesize * self::BYTES_IN_A_MEGABYTE) {
                throw new TransferException('filesize too large in zip');
            }
        }

        return $zip;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws TransferException
     */
    private function readManifest(ZipArchive $zip): array
    {
        $contents = $zip->getFromName('manifest.json');

        if ($contents === false) {
            throw new TransferException('manifest.json missing from zip');
        }

        $manifest = $this->decodeJson($contents);

        if (($manifest['format'] ?? null) !== BundleExporter::FORMAT_NAME) {
            throw new TransferException('unsupported file format');
        }

        if (($manifest['version'] ?? null) !== BundleExporter::FORMAT_VERSION) {
            throw new TransferException('unsupported format version');
        }

        return $manifest;
    }

    /**
     * @return array<string, array<string, mixed>>
     *
     * @throws TransferException
     */
    private function readEntities(ZipArchive $zip): array
    {
        $entities = [];

        for ($i = 0; $i < $zip->count(); $i++) {
            $entryName = (string) $zip->getNameIndex($i);

            if (!Str::startsWith($entryName, 'entities/') || !Str::endsWith($entryName, '.json')) {
                continue;
            }

            $contents = $zip->getFromIndex($i);
            Assert::string($contents);

            $entity = $this->decodeJson($contents);

            $typeValue = $entity['type'] ?? null;
            $id = $entity['id'] ?? null;

            if (!is_string($typeValue) || TransferEntityType::tryFrom($typeValue) === null || !is_string($id) || !Str::isUuid($id)) {
                throw new TransferException(sprintf('invalid entity in zip: %s', $entryName));
            }

            $entities[$id] = $entity;
        }

        return $entities;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws TransferException
     */
    private function decodeJson(string $contents): array
    {
        try {
            $data = json_decode($contents, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new TransferException('invalid json in zip');
        }

        if (!is_array($data)) {
            throw new TransferException('invalid json structure in zip');
        }

        Assert::allString(array_keys($data));

        /** @var array<string, mixed> $decoded */
        $decoded = $data;

        return $decoded;
    }
}
