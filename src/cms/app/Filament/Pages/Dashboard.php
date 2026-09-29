<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Filament\Widgets\AllClearWidget;
use App\Filament\Widgets\AwaitingEstablishmentWidget;
use App\Filament\Widgets\MyApprovalsWidget;
use App\Filament\Widgets\OpenDataBreachesWidget;
use App\Filament\Widgets\OverdueItemsWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

use function __;

/**
 * The panel's landing page.
 *
 * Replaces Filament's stock dashboard, which showed only the framework's
 * "welcome" and version widgets — nothing about the register a user opened the
 * application to work on.
 */
class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    protected static ?int $navigationSort = -1;

    public function getTitle(): string
    {
        return __('dashboard.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.title');
    }

    /**
     * @return array<class-string<Widget>|WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return [
            MyApprovalsWidget::class,
            AwaitingEstablishmentWidget::class,
            OpenDataBreachesWidget::class,
            OverdueItemsWidget::class,
            AllClearWidget::class,
        ];
    }

    /**
     * @return int|array<string, ?int>
     */
    public function getColumns(): int|array
    {
        return 1;
    }
}
