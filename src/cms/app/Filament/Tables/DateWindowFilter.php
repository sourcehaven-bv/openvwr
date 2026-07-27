<?php

declare(strict_types=1);

namespace App\Filament\Tables;

use App\Services\Dashboard\DateWindow;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

use function __;
use function is_string;

/**
 * Narrows a date column to what needs attention: dates already past, or coming
 * up within a lead time. Offering both is what makes the column actionable —
 * "verlopen" alone only ever reports work that is already too late.
 *
 * The boundary rules live in DateWindow so this filter and the dashboard counts
 * that link to it can never disagree.
 */
class DateWindowFilter extends SelectFilter
{
    public const string OVERDUE = 'overdue';
    public const string SOON = 'soon';

    protected DateWindow $dateWindow;

    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'date_window')
            ->options([
                self::OVERDUE => __('dashboard.filter.overdue'),
                self::SOON => __('dashboard.filter.soon'),
            ])
            // Resolved per-request with the filter injected by type, so a
            // ->dateWindow() set after make() is still honoured.
            ->query(static function (Builder $query, array $data, DateWindowFilter $filter): void {
                $value = $data['value'] ?? null;

                if (!is_string($value)) {
                    return;
                }

                $column = $filter->getAttribute();
                $dateWindow = $filter->getDateWindow();

                // Narrows in place: BaseFilter::apply() discards whatever a query
                // callback returns and keeps mutating the builder it handed in.
                match ($value) {
                    self::OVERDUE => $dateWindow->overdue($query, $column),
                    self::SOON => $dateWindow->soon($query, $column),
                    default => $query,
                };
            });
    }

    public function dateWindow(DateWindow $dateWindow): static
    {
        $this->dateWindow = $dateWindow;

        return $this;
    }

    public function getDateWindow(): DateWindow
    {
        return $this->dateWindow ??= new DateWindow();
    }
}
