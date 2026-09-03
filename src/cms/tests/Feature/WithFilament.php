<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Authorization\Permission;
use App\Models\Organisation;
use App\Models\User;
use Filament\Facades\Filament;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;
use Tests\Helpers\PermissionTestHelper;
use Tests\Helpers\SessionTestHelper;

trait WithFilament
{
    final public function asFilamentUser(?User $user = null): static
    {
        if ($user === null) {
            $user = UserTestHelper::create();
        }

        $organisation = $user->organisations->first();
        if ($organisation === null) {
            $organisation = OrganisationTestHelper::create();
        }

        $this->withPermissions($user, Permission::cases());
        $this->withFilamentSession($user, $organisation);

        return $this;
    }

    final public function asFilamentOrganisationUser(Organisation $organisation): static
    {
        $user = UserTestHelper::createForOrganisation($organisation);

        return $this->asFilamentUser($user);
    }

    final public function withFilamentSession(User $user, Organisation $organisation): static
    {
        SessionTestHelper::setOtpValid();

        $this->be($user);

        // v5 attaches the tenant to a new record from a `creating` observer that
        // the panel registers while booting, and the panel only boots from the
        // SetUpPanel middleware -- which a Livewire unit test never runs. So it
        // is booted here, through the manager's own guarded entry point: that
        // one boots at most once per container, unlike calling Panel::boot()
        // directly, which re-registers the render hooks on every call.
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::bootCurrentPanel();

        Filament::setTenant($organisation);

        return $this;
    }

    /**
     * @param array<Permission> $permissions
     */
    final public function withPermissions(User $user, array $permissions): static
    {
        PermissionTestHelper::give($user, $permissions);

        return $this;
    }
}
