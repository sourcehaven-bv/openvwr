<?php

declare(strict_types=1);

namespace App\Filament\Pages\Manual;

use App\Enums\Authorization\Role;
use App\Facades\Authorization;
use App\Manual\Manual;
use App\Manual\Task;
use App\Manual\TaskCapability;
use Filament\Facades\Filament;
use Filament\Panel;
use Webmozart\Assert\Assert;

use function __;
use function array_filter;
use function array_values;

/**
 * What every page of the manual has in common.
 *
 * The manual stays inside the panel: that is what keeps authentication, the
 * one time password gate, tenant resolution and authorisation exactly the same
 * as for the rest of the application, without a second copy of that logic to
 * get wrong. What it does not keep is the panel's chrome. Filament resolves the
 * layout per page through getLayout(), so overriding that with a layout of our
 * own replaces the navigation wholesale: in the manual the left menu is the
 * manual's own table of contents and the topbar is stripped back to a title and
 * the way out.
 */
trait ManualPage
{
    private Manual $manual;

    /**
     * The takeover layout, in place of the panel's own. Filament asks the page
     * for its layout, so this is the whole mechanism: the manual keeps the
     * panel's authentication, one time password gate, tenant and authorisation,
     * and replaces only its chrome.
     */
    final public function getLayout(): string
    {
        return 'filament.manual.layout';
    }

    final public function bootManualPage(Manual $manual): void
    {
        $this->manual = $manual;
    }

    final public function manual(): Manual
    {
        return $this->manual;
    }

    final public function getTitle(): string
    {
        return __('general.manual');
    }

    /**
     * The url of the panel this manual belongs to, for the way back.
     */
    final public function exitUrl(): string
    {
        $panel = Filament::getCurrentPanel();
        Assert::isInstanceOf($panel, Panel::class);

        $url = $panel->getUrl();
        Assert::string($url);

        return $url;
    }

    /**
     * The roles the current user holds in this organisation. Roles never hide a
     * task - knowing who does what is part of understanding the process - they
     * only change the wording around it.
     *
     * @return array<Role>
     */
    final public function roles(): array
    {
        return array_values(array_filter(
            Role::cases(),
            static fn (Role $role): bool => Authorization::hasRole($role),
        ));
    }

    final public function capabilityFor(Task $task): TaskCapability
    {
        return $task->capabilityFor($this->roles());
    }
}
