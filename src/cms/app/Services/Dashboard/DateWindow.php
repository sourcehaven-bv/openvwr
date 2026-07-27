<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * The two halves of "this date needs attention": already past, and coming up
 * within a lead time.
 *
 * Single home for the boundary rules so the dashboard counts and the table
 * filters can never disagree about what "verlopen" means — a stat linking to a
 * filtered table that shows a different number is worse than no stat.
 *
 * The halves are disjoint and meet at today: a date before today is overdue, a
 * date from today up to the horizon is upcoming. Callers may therefore add the
 * two counts without double-counting.
 */
final readonly class DateWindow
{
    /**
     * Matches the longest notification lead time offered on documents
     * (document.notification_options.3_months_before), so a reminder a user sets
     * on a document and this window speak about the same period.
     */
    public const int DEFAULT_SOON_IN_MONTHS = 3;

    public function __construct(
        public int $soonInMonths = self::DEFAULT_SOON_IN_MONTHS,
    ) {
    }

    /**
     * Strictly before today. A date of today is still due rather than overdue,
     * and is reported by soon().
     *
     * @param Builder<covariant Model> $query
     *
     * @return Builder<covariant Model>
     */
    public function overdue(Builder $query, string $column): Builder
    {
        return $query->whereNotNull($column)
            ->whereDate($column, '<', CarbonImmutable::today());
    }

    /**
     * Today up to and including the horizon.
     *
     * @param Builder<covariant Model> $query
     *
     * @return Builder<covariant Model>
     */
    public function soon(Builder $query, string $column): Builder
    {
        $today = CarbonImmutable::today();

        return $query->whereNotNull($column)
            ->whereDate($column, '>=', $today)
            ->whereDate($column, '<=', $today->addMonths($this->soonInMonths));
    }
}
