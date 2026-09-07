<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Enums\Import\MappingTransform;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

use function explode;
use function filter_var;
use function is_array;
use function is_bool;
use function is_scalar;
use function mb_strtolower;
use function trim;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_INT;

/**
 * Applies a MappingProfile to a single source row.
 *
 * The engine only rewrites keys and normalises values; creating models is left
 * to the existing factories, so a mapped row is indistinguishable from a row
 * that came out of a hand-written json export.
 */
class MappingEngine
{
    /**
     * The one format the engine emits, so every consumer parses the same thing.
     */
    private const DATE_FORMAT = 'Y-m-d\TH:i:s';

    /**
     * @param MappingProfile<covariant Model> $profile
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function apply(MappingProfile $profile, array $row): array
    {
        $mapped = [];

        foreach ($profile->fields as $field) {
            $value = $this->transform(Arr::get($row, $field->source), $field->transform, $field->trueDate);

            $mapped[$field->target] = $value;
        }

        return $mapped;
    }

    private function transform(mixed $value, MappingTransform $transform, ?string $trueDate = null): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($transform) {
            MappingTransform::BooleanToDate => $this->toDateFromBoolean($value, $trueDate),
            MappingTransform::Text => $this->toText($value),
            MappingTransform::Date => $this->toDate($value),
            MappingTransform::Boolean => $this->toBoolean($value),
            MappingTransform::Integer => $this->toInteger($value),
            MappingTransform::StringList => $this->toStringList($value),
        };
    }

    /**
     * "yes" becomes the configured date (or today when none was chosen);
     * anything else leaves the field empty rather than guessing a date.
     */
    private function toDateFromBoolean(mixed $value, ?string $trueDate): ?string
    {
        if ($this->toBoolean($value) !== true) {
            return null;
        }

        return $trueDate ?? CarbonImmutable::now()->format(self::DATE_FORMAT);
    }

    /**
     * Dates are parsed against a fixed list of formats rather than guessed:
     * "01/02/2026" is 1 February in a Dutch source, and a permissive parser
     * would silently make it 2 January. A value that matches no format stays
     * null, so the dry-run reports it instead of the register receiving a wrong
     * date.
     */
    private function toDate(mixed $value): ?string
    {
        $text = $this->toText($value);
        if ($text === null) {
            return null;
        }

        $timezone = Config::string('import.date.timezone');

        foreach (Config::array('import.mapping.date_formats') as $format) {
            try {
                // "!" resets the fields the format does not mention, so a date
                // without a time is midnight rather than the current time.
                $date = CarbonImmutable::rawCreateFromFormat('!' . $format, $text, $timezone);
            } catch (InvalidFormatException) {
                continue;
            }

            // PHP rolls "31-02-2026" over into March; only an exact round trip
            // proves the value really was a date in this format.
            if ($date === null || $date->format($format) !== $text) {
                continue;
            }

            return $date->format(self::DATE_FORMAT);
        }

        return null;
    }

    private function toText(mixed $value): ?string
    {
        if (is_array($value)) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Sources spell booleans in many ways; anything unrecognised stays null so
     * a bad value cannot silently read as "no".
     */
    private function toBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $text = $this->toText($value);
        if ($text === null) {
            return null;
        }

        return filter_var($text, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            ?? $this->toDutchBoolean($text);
    }

    private function toDutchBoolean(string $text): ?bool
    {
        return match (mb_strtolower($text)) {
            'ja', 'waar' => true,
            'nee', 'neen', 'onwaar' => false,
            default => null,
        };
    }

    private function toInteger(mixed $value): ?int
    {
        $text = $this->toText($value);
        if ($text === null) {
            return null;
        }

        $integer = filter_var($text, FILTER_VALIDATE_INT);

        return $integer === false ? null : $integer;
    }

    /**
     * @return array<int, string>|null
     */
    private function toStringList(mixed $value): ?array
    {
        if (is_array($value)) {
            $values = [];
            foreach ($value as $item) {
                $text = $this->toText($item);
                if ($text !== null) {
                    $values[] = $text;
                }
            }

            return $values === [] ? null : $values;
        }

        $text = $this->toText($value);
        if ($text === null) {
            return null;
        }

        $values = [];
        foreach (explode("\n", $text) as $item) {
            $item = trim($item);
            if ($item !== '') {
                $values[] = $item;
            }
        }

        return $values === [] ? null : $values;
    }
}
