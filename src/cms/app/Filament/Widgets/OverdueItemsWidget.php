<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Services\Dashboard\AttentionCountService;
use App\Services\Dashboard\OverdueItem;
use App\Services\DateFormatService;
use Filament\Widgets\Widget;

use function app;
use function count;

/**
 * Register items past their periodic review and documents past their expiry,
 * most overdue first.
 *
 * Until now these were only visible as a red cell inside three separate
 * register tables, with no notification and no way to filter, so finding them
 * meant opening each register and sorting it by hand.
 *
 * Hides itself when nothing is overdue.
 */
class OverdueItemsWidget extends Widget
{
    public const int LIMIT = 8;

    protected static ?int $sort = 2;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.overdue-items';

    /** @var array<string, array<int, OverdueItem>> */
    private static array $items = [];

    public static function canView(): bool
    {
        if (!Authorization::hasPermission(Permission::CORE_ENTITY_VIEW)) {
            return false;
        }

        return count(self::items()) > 0;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function getRows(): array
    {
        $rows = [];

        foreach (self::items() as $item) {
            $rows[] = [
                'name' => $item->name,
                'type' => $item->type,
                'kind' => $item->kind,
                'date' => DateFormatService::toDate($item->date) ?? '',
                'url' => $item->url,
            ];
        }

        return $rows;
    }

    public function hasMore(): bool
    {
        return count(self::items()) >= self::LIMIT;
    }

    /**
     * Resolved once per request: canView() and the view both need the rows.
     *
     * @return array<int, OverdueItem>
     */
    private static function items(): array
    {
        $organisation = Authentication::organisation();
        $cacheKey = $organisation->id->toString();

        if (!isset(self::$items[$cacheKey])) {
            self::$items[$cacheKey] = app(AttentionCountService::class)
                ->overdueItems($organisation, self::LIMIT);
        }

        return self::$items[$cacheKey];
    }
}
