<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Pages\TransferImport;
use App\Jobs\TransferImportJob;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the import page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(TransferImport::getUrl(tenant: $organisation))
        ->assertSee(__('transfer.import_help'));
});

it('cannot access the import page without the transfer import permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // the legacy import permission must not grant access to the transfer import
    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::CORE_ENTITY_IMPORT])
        ->get(TransferImport::getUrl(tenant: $organisation))
        ->assertForbidden();
});

it('can access the import page with the transfer import permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::TRANSFER_IMPORT])
        ->get(TransferImport::getUrl(tenant: $organisation))
        ->assertOk();
});

it('does not allow the client to tamper with the server-set bundle path', function (): void {
    $this->asFilamentUser();

    // bundlePath is #[Locked]; a client attempting to point it at another
    // organisation's export must be rejected outright.
    expect(fn () => $this->createLivewireTestable(TransferImport::class)
        ->set('bundlePath', 'transfer/exports/someone-else.zip'))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

it('does not import when no bundle has been analysed', function (): void {
    Bus::fake();

    $this->asFilamentUser()
        ->createLivewireTestable(TransferImport::class)
        ->call('cancel');

    Bus::assertNotDispatched(TransferImportJob::class);
});
