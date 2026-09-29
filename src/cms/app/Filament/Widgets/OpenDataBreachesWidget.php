<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Collections\DataBreachRecordCollection;
use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Resources\DataBreachRecordResource;
use App\Services\Dashboard\AttentionCountService;
use App\Services\Dashboard\DataBreachProgress;
use Filament\Widgets\Widget;

use function app;

/**
 * Data breaches whose handling is not yet finished.
 *
 * A breach stays on the list until completed_at is filled, which is the
 * register's own record of the work being done. The list deliberately does not
 * assert whether a breach had to be reported to the Autoriteit
 * Persoonsgegevens — that depends on a risk assessment kept as free text — but
 * it does mark a breach that is still open, not reported, and past the 72-hour
 * mark of Article 33, because that is the point by which the decision should
 * have been made.
 *
 * Hides itself when every breach has been handled.
 */
class OpenDataBreachesWidget extends Widget
{
    /**
     * Enough rows to plan a morning's work. Beyond that the list stops being a
     * worklist and becomes the register it links to.
     */
    public const int LIMIT = 8;

    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = 'full';
    protected string $view = 'filament.widgets.open-data-breaches';

    /** @var array<string, DataBreachRecordCollection> */
    private static array $records = [];

    public static function canView(): bool
    {
        if (!Authorization::hasPermission(Permission::DATA_BREACH_RECORD_VIEW)) {
            return false;
        }

        return self::records()->isNotEmpty();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getRows(): array
    {
        $rows = [];

        foreach (self::records() as $dataBreachRecord) {
            $progress = DataBreachProgress::for($dataBreachRecord);

            $rows[] = [
                'number' => $dataBreachRecord->entityNumber?->number,
                'name' => $dataBreachRecord->name,
                'discovered' => $progress->discoveredLabel(),
                'status' => $progress->statusLabel(),
                'isUrgent' => $progress->needsUrgentAttention(),
                'url' => DataBreachRecordResource::getUrl('edit', ['record' => $dataBreachRecord]),
            ];
        }

        return $rows;
    }

    public function getAllUrl(): string
    {
        return DataBreachRecordResource::getUrl(parameters: [
            'tableFilters' => ['open' => ['value' => '1']],
        ]);
    }

    public function hasMore(): bool
    {
        return self::records()->count() >= self::LIMIT;
    }

    /**
     * Resolved once per request: canView() and the view both need the rows, and
     * Filament calls them separately.
     */
    private static function records(): DataBreachRecordCollection
    {
        $organisation = Authentication::organisation();
        $cacheKey = $organisation->id->toString();

        if (!isset(self::$records[$cacheKey])) {
            self::$records[$cacheKey] = app(AttentionCountService::class)
                ->openDataBreachRecords($organisation, self::LIMIT);
        }

        return self::$records[$cacheKey];
    }
}
