<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

use function __;

/**
 * Splits data breaches on whether they are still being handled. A breach counts
 * as open until completed_at is filled, which is the only field that marks the
 * handling as finished.
 */
class OpenDataBreachFilter extends TernaryFilter
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'open')
            ->label(__('dashboard.filter.open_data_breach'))
            ->queries(
                true: static fn (Builder $query): Builder => $query->whereNull('completed_at'),
                false: static fn (Builder $query): Builder => $query->whereNotNull('completed_at'),
                blank: static fn (Builder $query): Builder => $query,
            );
    }
}
