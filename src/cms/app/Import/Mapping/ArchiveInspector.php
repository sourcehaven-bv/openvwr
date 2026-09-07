<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Import\ImportFailedException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

use function __;
use function array_key_exists;
use function count;
use function is_array;
use function is_string;
use function json_decode;
use function ksort;
use function sprintf;
use function sys_get_temp_dir;

/**
 * Reports what a native OpenVWR archive contains, so the user can see which
 * registers are about to be filled before anything is written.
 *
 * Reading only; the import itself stays with ZipImporter.
 */
class ArchiveInspector
{
    /**
     * @param array<string, class-string> $factories folder name => factory
     */
    public function __construct(
        private readonly ZipArchive $zipArchive,
        private readonly array $factories,
    ) {
    }

    /**
     * @return array<string, int> register name => number of records found
     *
     * @throws ImportFailedException
     */
    public function inspect(string $contents): array
    {
        $tempfilePath = sprintf('%s/%s.zip', sys_get_temp_dir(), Str::uuid()->toString());
        File::put($tempfilePath, $contents);

        try {
            return $this->readArchive($tempfilePath);
        } finally {
            File::delete($tempfilePath);
        }
    }

    /**
     * @return array<string, int>
     *
     * @throws ImportFailedException
     */
    private function readArchive(string $path): array
    {
        if ($this->zipArchive->open($path) !== true) {
            throw new ImportFailedException(__('import_mapping.error.unreadable_archive'));
        }

        $found = [];

        try {
            // The same limits the importer itself applies, so inspecting an
            // archive can never cost more than importing it would.
            if ($this->zipArchive->count() > Config::integer('import.max_number_of_files_in_zip')) {
                throw new ImportFailedException(__('import_mapping.error.archive_too_many_files'));
            }

            $maxBytes = Config::integer('import.max_zipped_file_filesize_in_mb') * 1024 * 1024;

            for ($i = 0; $i < $this->zipArchive->count(); $i++) {
                // An unreadable name is an empty one, which no register claims.
                $name = (string) $this->zipArchive->getNameIndex($i);

                $stat = $this->zipArchive->statIndex($i);

                if (is_array($stat) && $stat['size'] > $maxBytes) {
                    throw new ImportFailedException(__('import_mapping.error.archive_entry_too_large'));
                }

                // Zip entries always use a forward slash, whatever the platform.
                $register = Str::of($name)->explode('/')->first();

                if (!is_string($register) || !array_key_exists($register, $this->factories)) {
                    continue;
                }

                $found[$register] = ($found[$register] ?? 0) + $this->countRecords($i, $name);
            }
        } finally {
            $this->zipArchive->close();
        }

        ksort($found);

        return $found;
    }

    /**
     * Counts the records in one entry. A json file holds a list; anything else
     * counts as a single entry so the total stays meaningful.
     */
    private function countRecords(int $index, string $name): int
    {
        if (Str::lower(File::extension($name)) !== 'json') {
            return 1;
        }

        // An unreadable entry decodes to nothing and counts as nothing.
        $data = json_decode((string) $this->zipArchive->getFromIndex($index), true);

        return is_array($data) ? count($data) : 0;
    }
}
