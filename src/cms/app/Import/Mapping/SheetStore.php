<?php

declare(strict_types=1);

namespace App\Import\Mapping;

use App\Facades\Authentication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

use function is_array;
use function now;
use function sprintf;

/**
 * Keeps the rows of an uploaded sheet server-side for the rest of the
 * session, bound to the user who uploaded them. A sheet of a few thousand
 * rows would otherwise travel with every change to a dropdown.
 */
class SheetStore
{
    /**
     * @param array<int, array<string, mixed>> $rows
     *
     * @return string the key the rows are kept under
     */
    public function put(array $rows): string
    {
        $key = sprintf('import-mapping:%s:%s', Authentication::user()->id->toString(), Str::uuid()->toString());

        Cache::put($key, $rows, now()->addMinutes(Config::integer('import.mapping.sheet_ttl_minutes')));

        return $key;
    }

    /**
     * @return array<int, array<string, mixed>> empty when the rows are gone
     */
    public function rows(?string $key): array
    {
        $rows = $key === null ? [] : Cache::get($key);

        /** @var array<int, array<string, mixed>> $rows */
        $rows = is_array($rows) ? $rows : [];

        return $rows;
    }

    public function forget(?string $key): void
    {
        if ($key !== null) {
            Cache::forget($key);
        }
    }
}
