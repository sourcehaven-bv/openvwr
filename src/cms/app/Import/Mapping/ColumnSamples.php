<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

use function count;
use function implode;
use function in_array;
use function is_array;
use function is_scalar;
use function trim;

/**
 * A few distinct values from a column, so the user can judge a mapping by
 * what is actually in the column instead of by its heading alone.
 */
final class ColumnSamples
{
    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return array<int, string>
     */
    public static function for(array $rows, string $header, int $limit): array
    {
        $samples = [];

        foreach ($rows as $row) {
            $value = self::text(Arr::get($row, $header));

            if ($value === null || in_array($value, $samples, true)) {
                continue;
            }

            $samples[] = $value;

            if (count($samples) >= $limit) {
                break;
            }
        }

        return $samples;
    }

    private static function text(mixed $value): ?string
    {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $item) {
                if (is_scalar($item)) {
                    $parts[] = (string) $item;
                }
            }

            $value = implode(', ', $parts);
        }

        if (!is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : Str::limit($value, 80);
    }
}
