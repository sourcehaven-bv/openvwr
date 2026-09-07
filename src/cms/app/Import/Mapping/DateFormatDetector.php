<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\Config;

use function __;
use function count;
use function strcspn;
use function trim;

/**
 * Works out how a source writes its dates.
 *
 * A format is decided per column, not per cell: "04-03-2026" is 4 March in a
 * Dutch export and 3 April in an American one, and only the column as a whole
 * can tell. A format is a candidate when every sample value fits it exactly.
 * One candidate is a finding; several are a question for the user.
 */
class DateFormatDetector
{
    /**
     * The one format the engine emits, so every consumer parses the same thing.
     */
    public const OUTPUT_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * Formats from import.mapping.date_formats that every sample fits.
     *
     * @param array<int, string> $samples
     *
     * @return array<int, string>
     */
    public function candidates(array $samples): array
    {
        $candidates = [];

        foreach ($this->formats() as $format) {
            if ($this->fitsAll($format, $samples)) {
                $candidates[] = $format;
            }
        }

        return $candidates;
    }

    /**
     * The format when there is no doubt about it, null otherwise.
     *
     * @param array<int, string> $samples
     */
    public function detect(array $samples): ?string
    {
        $candidates = $this->candidates($samples);

        return count($candidates) === 1 ? $candidates[0] : null;
    }

    /**
     * Parses one value against one format. Null when it does not fit: PHP
     * rolls "31-02-2026" over into March, so only an exact round trip counts.
     */
    public function parse(string $value, string $format): ?CarbonImmutable
    {
        $value = trim($value);

        try {
            // "!" resets the fields the format does not mention, so a date
            // without a time is midnight rather than the current time.
            $date = CarbonImmutable::rawCreateFromFormat('!' . $format, $value, Config::string('import.date.timezone'));
        } catch (InvalidFormatException) {
            return null;
        }

        if ($date === null || $date->format($format) !== $value) {
            return null;
        }

        return $date;
    }

    /**
     * Parses against the configured formats in order, first fit wins. Only for
     * values whose column has no format of its own, such as a profile saved
     * before formats were recorded.
     */
    public function parseAny(string $value): ?CarbonImmutable
    {
        foreach ($this->formats() as $format) {
            $date = $this->parse($value, $format);

            if ($date !== null) {
                return $date;
            }
        }

        return null;
    }

    /**
     * Which part comes first, in words, so two formats that render a sample the
     * same way ("12-12-2026") can still be told apart.
     */
    public function describe(string $format): string
    {
        $year = strcspn($format, 'Y');
        $day = strcspn($format, 'dj');
        $month = strcspn($format, 'mn');

        if ($year < $day && $year < $month) {
            return __('import_mapping.date_order.year_first');
        }

        return $day < $month
            ? __('import_mapping.date_order.day_first')
            : __('import_mapping.date_order.month_first');
    }

    /**
     * @return array<int, string>
     */
    public function formats(): array
    {
        /** @var array<int, string> $formats */
        $formats = Config::array('import.mapping.date_formats');

        return $formats;
    }

    /**
     * @param array<int, string> $samples
     */
    private function fitsAll(string $format, array $samples): bool
    {
        $seen = false;

        foreach ($samples as $sample) {
            if (trim($sample) === '') {
                continue;
            }

            $seen = true;

            if ($this->parse($sample, $format) === null) {
                return false;
            }
        }

        return $seen;
    }
}
