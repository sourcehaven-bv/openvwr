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
        // the panel registers, and that observer only fires while that same
        // panel is the current one. Outside a request nothing sets either, so a
        // plain setTenant() would leave organisation_id null and the insert
        // would hit the not-null constraint.
        //
        // Only the tenancy registration is repeated here, not Panel::boot():
        // boot() also re-registers the render hooks and the databaseTransaction
        // callback every time, and it has no guard against running twice, so
        // calling it per test piles those up across the suite.
        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);

        foreach ($panel->getResources() as $resource) {
            $resource::observeTenancyModelCreation($panel);
            $resource::registerTenancyModelGlobalScope($panel);
        }

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
