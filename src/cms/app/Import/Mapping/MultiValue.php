<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function array_map;
use function array_slice;
use function count;
use function explode;
use function implode;
use function in_array;
use function is_array;
use function is_string;
use function mb_strtolower;
use function preg_replace;
use function str_contains;
use function trim;

/**
 * Splits a cell that holds several values.
 *
 * One value per line is the convention, but OpenVWR's own Excel export (and
 * most other tools) writes lists as "Naam, Adres". A cell without line breaks
 * is therefore split on a comma followed by a space as well; a name holding
 * such a comma is rare, and a split name shows up in the import report as a
 * newly created record, where it can be caught.
 */
final class MultiValue
{
    private const LIST_SEPARATOR = ', ';

    /**
     * @param non-empty-string $separator
     * @param array<int, string> $options the values the cell may hold when it
     *        is a fixed choice; a choice that itself contains ", " ("Hacking,
     *        malware en/of phishing") is then kept whole
     *
     * @return array<int, string> trimmed, without empty entries
     */
    public static function split(string $cell, string $separator = "\n", array $options = []): array
    {
        $parts = self::parts($cell, $separator);

        if (count($parts) === 1 && $separator !== self::LIST_SEPARATOR && str_contains($cell, self::LIST_SEPARATOR)) {
            return self::rejoinKnown(self::parts($cell, self::LIST_SEPARATOR), $options);
        }

        return $parts;
    }

    /**
     * Puts back together the pieces of a choice that was cut at its own comma:
     * the longest run of adjacent pieces that spells a known choice wins.
     *
     * @param array<int, string> $parts
     * @param array<int, string> $options
     *
     * @return array<int, string>
     */
    private static function rejoinKnown(array $parts, array $options): array
    {
        if ($options === []) {
            return $parts;
        }

        $known = array_map(static fn (string $option): string => mb_strtolower(trim($option)), $options);
        $joined = [];
        $count = count($parts);

        for ($start = 0; $start < $count; $start++) {
            $taken = 1;

            for ($end = $count; $end > $start + 1; $end--) {
                $candidate = implode(self::LIST_SEPARATOR, array_slice($parts, $start, $end - $start));

                if (in_array(mb_strtolower($candidate), $known, true)) {
                    $taken = $end - $start;

                    break;
                }
            }

            $joined[] = implode(self::LIST_SEPARATOR, array_slice($parts, $start, $taken));
            $start += $taken - 1;
        }

        return $joined;
    }

    /**
     * The entries a cell contributes to a list. A record folded from several
     * rows holds one entry per row, blanks included, so the third name still
     * meets the third e-mail address; a plain cell is split as usual.
     *
     * @param non-empty-string $separator
     *
     * @return array<int, string>
     */
    public static function entries(mixed $cell, string $separator = "\n"): array
    {
        if (is_array($cell)) {
            $entries = [];
            foreach ($cell as $entry) {
                $entries[] = is_string($entry) ? trim($entry) : '';
            }

            return $entries;
        }

        if (!is_string($cell)) {
            return [];
        }

        // An export joins a list with ", " and leaves a blank where a record
        // has no value; the blank has to stay so the positions hold. A blank
        // at the end survives only as a trailing comma once the cell is
        // trimmed, so that comma is read as a separator too.
        $cell = preg_replace('/,\s*$/', self::LIST_SEPARATOR, $cell) ?? $cell;
        $actual = str_contains($cell, $separator) || !str_contains($cell, self::LIST_SEPARATOR)
            ? $separator
            : self::LIST_SEPARATOR;

        return array_map(trim(...), explode($actual, $cell));
    }

    /**
     * @param non-empty-string $separator
     *
     * @return array<int, string>
     */
    private static function parts(string $cell, string $separator): array
    {
        $parts = [];

        foreach (explode($separator, $cell) as $part) {
            $part = trim($part);

            if ($part !== '') {
                $parts[] = $part;
            }
        }

        return $parts;
    }
}
