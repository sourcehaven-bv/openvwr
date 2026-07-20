<?php

declare(strict_types=1);

use App\Filament\Resources\ContactPersonPositionResource\Pages\ListContactPersonPositions;
use App\Models\ContactPersonPosition;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $contactPersonPositions = ContactPersonPosition::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListContactPersonPositions::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($contactPersonPositions);
});
