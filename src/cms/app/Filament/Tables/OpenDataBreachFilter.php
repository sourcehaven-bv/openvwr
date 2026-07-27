<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Models\States\DataBreachRecord\Closed;
use App\Models\States\DataBreachRecord\NoBreach;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;

use function __;

/**
 * Splits data breaches on whether they are still being handled.
 *
 * A breach is finished once the state machine says so — closed, or assessed as
 * not being a breach — or once completed_at is filled. That second check covers
 * records from before the state machine existed, which can be finished while
 * still sitting in the default state.
 */
class OpenDataBreachFilter extends TernaryFilter
{
    private const array FINISHED_STATES = [Closed::class, NoBreach::class];

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'open')
            ->label(__('dashboard.filter.open_data_breach'))
            ->queries(
                true: static fn (Builder $query): Builder => $query
                    ->whereNull('completed_at')
                    ->whereNotIn('state', self::finishedStateNames()),
                false: static fn (Builder $query): Builder => $query
                    ->where(static function (Builder $query): void {
                        $query->whereNotNull('completed_at')
                            ->orWhereIn('state', self::finishedStateNames());
                    }),
                blank: static fn (Builder $query): Builder => $query,
            );
    }

    /**
     * @return array<int, string>
     */
    private static function finishedStateNames(): array
    {
        $names = [];

        foreach (self::FINISHED_STATES as $state) {
            $names[] = $state::$name;
        }

        return $names;
    }
}
