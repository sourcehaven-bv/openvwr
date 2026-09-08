<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

use function count;
use function is_scalar;
use function trim;

/**
 * Folds the rows of one record into a single row.
 *
 * A plain field takes the one value the rows agree on; rows that disagree are
 * reported, because a record cannot hold two names. A link or a note keeps
 * one entry per row, blanks included, so a name on row three still lines up
 * with the e-mail address on row three.
 */
class RecordGrouper
{
    /**
     * One entry per record. Without an identity column every row is a record
     * of its own, and the row comes back as it is.
     *
     * @param MappingProfile<covariant Model> $profile
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, array{number: int, numbers: array<int, int>, row: array<string, mixed>, conflicts: array<int, string>}>
     */
    public function group(MappingProfile $profile, array $rows): array
    {
        $records = [];

        foreach (self::groups($rows, $profile->identity) as $indexes) {
            $records[] = $this->merge($profile, $rows, $indexes);
        }

        return $records;
    }

    /**
     * How many records the rows make once those of one record are folded.
     *
     * @param array<int, array<string, mixed>> $rows
     */
    public static function recordCount(array $rows, ?string $identity): int
    {
        return count(self::groups($rows, $identity));
    }

    /**
     * Row indexes per record. A row without a value in the identity column
     * is a record of its own: there is nothing to attach it to.
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<string, array<int, int>>
     */
    private static function groups(array $rows, ?string $identity): array
    {
        $groups = [];

        foreach ($rows as $index => $row) {
            $value = $identity === null ? null : Arr::get($row, $identity);
            $text = is_scalar($value) ? trim((string) $value) : '';
            $groups[$text === '' ? '#' . $index : 'k:' . $text][] = $index;
        }

        return $groups;
    }

    /**
     * @param MappingProfile<covariant Model> $profile
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $indexes
     *
     * @return array{number: int, numbers: array<int, int>, row: array<string, mixed>, conflicts: array<int, string>}
     */
    private function merge(MappingProfile $profile, array $rows, array $indexes): array
    {
        $numbers = [];
        foreach ($indexes as $index) {
            $numbers[] = $index + 1;
        }

        if (count($indexes) === 1) {
            return ['number' => $numbers[0], 'numbers' => $numbers, 'row' => $rows[$indexes[0]], 'conflicts' => []];
        }

        $merged = $rows[$indexes[0]];
        $conflicts = [];

        foreach ($profile->fields as $field) {
            if ($this->collectsPerRow($field)) {
                $merged[$field->source] = $this->perRow($rows, $indexes, $field->source);

                continue;
            }

            [$value, $agreed] = $this->single($rows, $indexes, $field->source);
            $merged[$field->source] = $value;

            if (!$agreed) {
                $conflicts[] = $field->source;
            }
        }

        return ['number' => $numbers[0], 'numbers' => $numbers, 'row' => $merged, 'conflicts' => $conflicts];
    }

    /**
     * Links, their attributes and notes take one entry per row; a plain field
     * or a lookup takes one value for the record.
     */
    private function collectsPerRow(MappingField $field): bool
    {
        return $field->relation !== null && !RelationKey::isLookup($field->relation);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $indexes
     *
     * @return array<int, string|null>
     */
    private function perRow(array $rows, array $indexes, string $source): array
    {
        $values = [];

        foreach ($indexes as $index) {
            $value = Arr::get($rows[$index], $source);
            $text = is_scalar($value) ? trim((string) $value) : '';
            $values[] = $text === '' ? null : $text;
        }

        return $values;
    }

    /**
     * The first value the rows supply, and whether every row that supplies
     * one supplies the same.
     *
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, int> $indexes
     *
     * @return array{0: mixed, 1: bool}
     */
    private function single(array $rows, array $indexes, string $source): array
    {
        $chosen = null;
        $agreed = true;

        foreach ($indexes as $index) {
            $value = Arr::get($rows[$index], $source);

            if ($value === null || (is_scalar($value) && trim((string) $value) === '')) {
                continue;
            }

            if ($chosen === null) {
                $chosen = $value;

                continue;
            }

            if ($value !== $chosen) {
                $agreed = false;
            }
        }

        return [$chosen, $agreed];
    }
}
