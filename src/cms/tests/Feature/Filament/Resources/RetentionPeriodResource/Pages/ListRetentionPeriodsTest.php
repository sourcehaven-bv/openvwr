<?php

declare(strict_types=1);

use App\Filament\Resources\RetentionPeriodResource\Pages\ListRetentionPeriods;
use App\Models\RetentionPeriod;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $retentionPeriods = RetentionPeriod::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListRetentionPeriods::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($retentionPeriods);
});
