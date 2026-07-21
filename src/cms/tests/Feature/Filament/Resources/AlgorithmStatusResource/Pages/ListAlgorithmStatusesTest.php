<?php

declare(strict_types=1);

use App\Filament\Resources\AlgorithmStatusResource\Pages\ListAlgorithmStatuses;
use App\Models\Algorithm\AlgorithmStatus;
use Tests\Helpers\Model\OrganisationTestHelper;

it('can load the list resource page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $records = AlgorithmStatus::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAlgorithmStatuses::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($records);
});
