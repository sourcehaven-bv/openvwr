<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use Filament\Widgets\Widget;

/**
 * Shown only when every attention list is empty.
 *
 * Without it, a user with nothing to do gets a dashboard that is literally
 * blank, which reads as a broken page rather than as good news. This says the
 * quiet part out loud: there is nothing waiting for you.
 *
 * It asks each list widget whether it would render, so a new list added later
 * only has to be named here to be accounted for.
 */
class AllClearWidget extends Widget
{
    /**
     * The widgets whose absence means there is genuinely nothing to do.
     */
    private const array ATTENTION_WIDGETS = [
        OpenDataBreachesWidget::class,
        OverdueItemsWidget::class,
        MyApprovalsWidget::class,
    ];

    // Last, so that on the one render where it appears it is not competing with
    // anything; the sort is irrelevant when it is the only widget on the page.
    protected static ?int $sort = 99;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.all-clear';

    public static function canView(): bool
    {
        if (!self::hasAnyAttentionPermission()) {
            return false;
        }

        foreach (self::ATTENTION_WIDGETS as $widget) {
            if ($widget::canView()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the viewer can see any of the things the lists report on.
     *
     * Without this a user who simply has no register permissions — the
     * functional manager holds none — would be told that nothing requires their
     * attention, which reads as "your register is clean" when it means "you
     * cannot see the register at all".
     */
    private static function hasAnyAttentionPermission(): bool
    {
        $permissions = [
            Permission::CORE_ENTITY_VIEW,
            Permission::DOCUMENT_VIEW,
            Permission::DATA_BREACH_RECORD_VIEW,
            Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL,
        ];

        foreach ($permissions as $permission) {
            if (Authorization::hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
