<?php

declare(strict_types=1);

use App\Filament\Resources\AlgorithmPublicationCategoryResource\Pages\ListAlgorithmPublicationCategories;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use Tests\Helpers\Model\OrganisationTestHelper;

it('can load the list resource page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $records = AlgorithmPublicationCategory::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAlgorithmPublicationCategories::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($records);
});
