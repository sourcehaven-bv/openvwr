<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Import\ImportFailedException;
use DateTimeInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Reader\CSV\Options as CsvOptions;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\Exception\ReaderException;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;

use function __;
use function array_values;
use function count;
use function in_array;
use function is_string;
use function preg_match;
use function preg_replace;
use function sprintf;
use function str_starts_with;
use function substr_count;
use function sys_get_temp_dir;
use function trim;

/**
 * Reads a spreadsheet into plain arrays.
 *
 * Header cells may use dot-notation to describe nesting, so a flat sheet can
 * feed the same factories as a nested json export.
 *
 * Note that a spreadsheet only yields a date object for cells that carry a date
 * *style*. An unstyled date is indistinguishable from a plain number, so it is
 * left as-is rather than guessed at.
 */
class SheetReader
{
    /**
     * One of import.date.expectedFormats, so typed cells and text cells converge.
     */
    private const DATE_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * Delimiters a CSV may use, in order of preference when counts tie. Dutch
     * Excel writes semicolons, so a comma cannot simply be assumed.
     */
    private const CSV_DELIMITERS = [',', ';', "\t"];

    /**
     * @throws ImportFailedException
     */
    public function read(string $filename, string $contents): Sheet
    {
        $extension = Str::lower(File::extension($filename));

        if (!in_array($extension, ['xlsx', 'csv'], true)) {
            throw new ImportFailedException(__('import_mapping.error.unsupported_type'));
        }

        $tempfilePath = sprintf('%s/%s.%s', sys_get_temp_dir(), Str::uuid()->toString(), $extension);

        File::put($tempfilePath, $contents);

        try {
            return $this->readFile($extension, $tempfilePath, $contents);
        } catch (IOException | ReaderException $exception) {
            // The library message names the temp file; the user gets a plain
            // explanation and the detail goes to the log.
            Log::warning('could not read spreadsheet', ['exception' => $exception::class, 'message' => $exception->getMessage()]);

            throw new ImportFailedException(__('import_mapping.error.unreadable'));
        } finally {
            File::delete($tempfilePath);
        }
    }

    /**
     * @throws ImportFailedException
     * @throws IOException
     * @throws ReaderException
     */
    private function readFile(string $extension, string $tempfilePath, string $contents): Sheet
    {
        $reader = $extension === 'xlsx'
            ? new XlsxReader()
            : new CsvReader($this->csvOptions($contents));

        $reader->open($tempfilePath);

        try {
            // Both readers always yield at least one sheet.
            $sheets = $reader->getSheetIterator();
            $sheets->rewind();

            return $this->readRows($sheets->current()->getRowIterator());
        } finally {
            $reader->close();
        }
    }

    /**
     * Picks the delimiter that occurs most in the header line.
     */
    private function csvOptions(string $contents): CsvOptions
    {
        $options = new CsvOptions();
        $options->FIELD_DELIMITER = $this->sniffDelimiter(Str::before($contents, "\n"));

        return $options;
    }

    private function sniffDelimiter(string $line): string
    {
        $best = ',';
        $bestCount = -1;

        foreach (self::CSV_DELIMITERS as $delimiter) {
            $count = substr_count($line, $delimiter);

            if ($count <= $bestCount) {
                continue;
            }

            $best = $delimiter;
            $bestCount = $count;
        }

        return $best;
    }

    /**
     * @param iterable<Row> $rowIterator
     *
     * @throws ImportFailedException
     */
    private function readRows(iterable $rowIterator): Sheet
    {
        $headers = null;
        $rows = [];
        $maxRows = Config::integer('import.mapping.max_rows');

        foreach ($rowIterator as $row) {
            $values = $row->toArray();

            if ($headers === null) {
                $headers = $this->toHeaders($values);

                continue;
            }

            $dataSet = $this->toDataSet($headers, $values);
            if ($dataSet === null) {
                continue;
            }

            $rows[] = $dataSet;

            // Everything read here is held in memory and shown on screen, so a
            // sheet has to fit; beyond this the zip route is the right tool.
            if (count($rows) > $maxRows) {
                throw new ImportFailedException(__('import_mapping.error.too_many_rows', ['max' => $maxRows]));
            }
        }

        if ($headers === null) {
            throw new ImportFailedException(__('import_mapping.error.no_header'));
        }

        return new Sheet(array_values($headers), $rows);
    }

    /**
     * Templates often put instructions in the heading cell, below the name or
     * after a colon: "Omschrijving\nNoteer hier de naam van de verwerking".
     * Only the name is the column's name.
     */
    private function cleanHeader(string $value): string
    {
        $header = trim(Str::before($value, "\n"));

        // "Verwerkers: Noteer hier de namen van ..." -- a short part before the
        // colon followed by a longer sentence is a label with an instruction.
        if (preg_match('/^(.{2,40}?):\s+(.{20,})$/u', $header, $matches) === 1) {
            $header = trim($matches[1]);
        }

        // "Grondslag, meerdere keuzes mogelijk." and "(indien afwijkend van
        // het beleid)" describe how to fill the column in, not what it is.
        $header = preg_replace('/,?\s*meerdere (keuzes|antwoorden|opties) mogelijk\.?$/iu', '', $header) ?? $header;
        $header = preg_replace('/\s*\([^()]{12,}\)$/u', '', $header) ?? $header;

        // "Gemeld aan betrokkenen        Ja=1" -- a legend for the values,
        // padded with spaces to sit under the name.
        $header = preg_replace('/\s+(ja|nee|yes|no)\s*=\s*\d.*$/iu', '', $header) ?? $header;
        $header = preg_replace('/\s+/u', ' ', $header) ?? $header;

        return trim($header);
    }

    /**
     * Trailing empty columns are dropped so they cannot produce null-only keys.
     * Two columns with the same name, or a name that is also the prefix of a
     * dotted name, would silently overwrite each other's values, so those are
     * refused instead.
     *
     * @param array<int, mixed> $values
     *
     * @return array<int, string>
     *
     * @throws ImportFailedException
     */
    private function toHeaders(array $values): array
    {
        $headers = [];
        foreach ($values as $index => $value) {
            $header = is_string($value) ? $this->cleanHeader($value) : '';
            if ($header === '') {
                continue;
            }

            if (in_array($header, $headers, true)) {
                throw new ImportFailedException(__('import_mapping.error.duplicate_column', ['column' => $header]));
            }

            $headers[$index] = $header;
        }

        if ($headers === []) {
            throw new ImportFailedException(__('import_mapping.error.no_header'));
        }

        foreach ($headers as $header) {
            foreach ($headers as $other) {
                if (str_starts_with($other, $header . '.')) {
                    throw new ImportFailedException(__('import_mapping.error.column_conflict', [
                        'column' => $header,
                        'other' => $other,
                    ]));
                }
            }
        }

        return $headers;
    }

    /**
     * @param array<int, string> $headers
     * @param array<int, mixed> $values
     *
     * @return array<string, mixed>|null null when the row holds no values at all
     */
    private function toDataSet(array $headers, array $values): ?array
    {
        $dataSet = [];
        $hasValue = false;

        foreach ($headers as $index => $header) {
            $value = $this->normalizeValue($values[$index] ?? null);

            if ($value !== null) {
                $hasValue = true;
            }

            $dataSet[$header] = $value;
        }

        if (!$hasValue) {
            return null;
        }

        /** @var array<string, mixed> $undotted */
        $undotted = Arr::undot($dataSet);

        return $undotted;
    }

    /**
     * Blank cells become null so a missing value never reaches a factory as ''.
     *
     * Typed date cells are rendered in the format the existing converters expect,
     * because DataConverters::toCarbon() asserts a string.
     */
    private function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(self::DATE_FORMAT);
        }

        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return $value;
    }
}
