<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Support\Arr;

use function array_search;
use function count;
use function in_array;
use function is_scalar;
use function json_encode;
use function trim;

/**
 * Notices when a sheet holds one record per *group* of rows rather than per
 * row.
 *
 * Register tools that flatten a relational model to a sheet repeat the
 * record's own columns on every row and give each row one item of one list:
 * a system on this row, a goal on the next. The tell is a column whose value
 * repeats while every column that is filled on all of those rows agrees, and
 * only the sparsely filled columns differ. Such a column is proposed as the
 * one that identifies a record; the user confirms it.
 */
class RecordGrouping
{
    /**
     * Column names that usually identify a record, most convincing first.
     */
    private const KEY_LIKE = ['nummer', 'naam'];

    public function __construct(
        private readonly FieldSynonyms $fieldSynonyms,
    ) {
    }

    /**
     * The column whose repeated values mark the rows of one record, or null
     * when every row stands for a record of its own.
     *
     * @param array<int, string> $headers
     * @param array<int, array<string, mixed>> $rows
     */
    public function detect(array $headers, array $rows): ?string
    {
        if (count($rows) < 2) {
            return null;
        }

        $best = null;

        foreach ($headers as $header) {
            $groups = $this->groupsBy($rows, $header);

            if ($groups === null || count($groups) === count($rows) || !$this->rowsAgree($rows, $groups, $headers, $header)) {
                continue;
            }

            $candidate = ['header' => $header, 'rank' => $this->keyRank($header)];

            if ($best === null || $candidate['rank'] < $best['rank']) {
                $best = $candidate;
            }
        }

        return $best['header'] ?? null;
    }

    /**
     * Row indexes per distinct value; null when a row has no value, because a
     * key is never blank.
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<string, array<int, int>>|null
     */
    private function groupsBy(array $rows, string $header): ?array
    {
        $groups = [];

        foreach ($rows as $index => $row) {
            $value = $this->cell($row, $header);

            if ($value === null) {
                return null;
            }

            $groups[$value][] = $index;
        }

        return $groups;
    }

    /**
     * Within every group, a column that has a value on each row must have the
     * same value on each row. Columns that are blank on some rows carry the
     * list items and may differ.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<string, array<int, int>> $groups
     * @param array<int, string> $headers
     */
    private function rowsAgree(array $rows, array $groups, array $headers, string $key): bool
    {
        foreach ($groups as $indexes) {
            if (count($indexes) < 2) {
                continue;
            }

            foreach ($headers as $header) {
                if ($header === $key) {
                    continue;
                }

                if ($this->filledEverywhere($rows, $indexes, $header) && $this->differ($rows, $indexes, $header)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $indexes
     */
    private function filledEverywhere(array $rows, array $indexes, string $header): bool
    {
        foreach ($indexes as $index) {
            if ($this->cell($rows[$index], $header) === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $indexes
     */
    private function differ(array $rows, array $indexes, string $header): bool
    {
        $first = $this->cell($rows[$indexes[0]], $header);

        foreach ($indexes as $index) {
            if ($this->cell($rows[$index], $header) !== $first) {
                return true;
            }
        }

        return false;
    }

    /**
     * Several columns can mark the rows of a record (the id, the name, the
     * status), and every column that qualifies marks the same rows: one that
     * divided them further would have disagreed within a group. So only the
     * name decides: 0 for a column called nummer/id/kenmerk, 1 for a name, 2
     * for the rest, and the leftmost wins a tie.
     */
    private function keyRank(string $header): int
    {
        $canonical = $this->fieldSynonyms->canonicalise($header);

        return in_array($canonical, self::KEY_LIKE, true)
            ? (int) array_search($canonical, self::KEY_LIKE, true)
            : count(self::KEY_LIKE);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function cell(array $row, string $header): ?string
    {
        $value = Arr::get($row, $header);

        if ($value === null) {
            return null;
        }

        $text = is_scalar($value) ? trim((string) $value) : (string) json_encode($value);

        return $text === '' ? null : $text;
    }
}
