<?php

declare(strict_types=1);

use App\Filament\Resources\AlgorithmThemeResource\Pages\ListAlgorithmThemes;
use App\Models\Algorithm\AlgorithmTheme;
use Tests\Helpers\Model\OrganisationTestHelper;

it('can load the list resource page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $records = AlgorithmTheme::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAlgorithmThemes::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($records);
});
