<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use Filament\Widgets\Widget;

/**
 * Shown whenever every attention list is empty.
 *
 * Without it, a user with nothing to do gets a dashboard that is literally
 * blank, which reads as a broken page rather than as good news. This says the
 * quiet part out loud: there is nothing waiting for you.
 *
 * It asks each list widget whether it would render, so a new list added later
 * only has to be named here to be accounted for.
 *
 * Someone who may see no register at all still gets a message; only the wording
 * changes, via hasRegisterAccess(). Rendering nothing for them would leave the
 * page blank, which is the failure this widget exists to prevent.
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
        AwaitingEstablishmentWidget::class,
    ];

    // Last, so that on the one render where it appears it is not competing with
    // anything; the sort is irrelevant when it is the only widget on the page.
    protected static ?int $sort = 99;
    protected int|string|array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.all-clear';

    public static function canView(): bool
    {
        foreach (self::ATTENTION_WIDGETS as $widget) {
            if ($widget::canView()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Whether the viewer can see the register the message talks about.
     *
     * Drives the wording rather than whether the widget renders: a functional
     * manager holds no register permissions, so telling them the register is
     * clean would state something they cannot know. They still get a message —
     * an empty page reads as broken — but one about their own dashboard.
     */
    public function hasRegisterAccess(): bool
    {
        return self::hasAnyAttentionPermission();
    }

    /**
     * Whether the viewer can see any of the things the lists report on.
     */
    private static function hasAnyAttentionPermission(): bool
    {
        $permissions = [
            Permission::CORE_ENTITY_VIEW,
            Permission::DOCUMENT_VIEW,
            Permission::DATA_BREACH_RECORD_VIEW,
            Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL,
            Permission::SNAPSHOT_STATE_TO_ESTABLISHED,
        ];

        foreach ($permissions as $permission) {
            if (Authorization::hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
