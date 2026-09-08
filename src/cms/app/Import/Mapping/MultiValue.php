<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use function array_map;
use function count;
use function explode;
use function is_array;
use function is_string;
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
     *
     * @return array<int, string> trimmed, without empty entries
     */
    public static function split(string $cell, string $separator = "\n"): array
    {
        $parts = self::parts($cell, $separator);

        if (count($parts) === 1 && $separator !== self::LIST_SEPARATOR && str_contains($cell, self::LIST_SEPARATOR)) {
            return self::parts($cell, self::LIST_SEPARATOR);
        }

        return $parts;
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
        // has no value; the blank has to stay so the positions hold.
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
