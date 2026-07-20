<?php

declare(strict_types=1);

use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages\ListAvgResponsibleProcessingRecordServices;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecordServices::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($avgResponsibleProcessingRecordService);
});

it('loads the enabled page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $enabledAvgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $disabledAvgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => false]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecordServices::class)
        ->set('activeTab', 'enabled')
        ->assertCanSeeTableRecords([$enabledAvgResponsibleProcessingRecordService])
        ->assertCanNotSeeTableRecords([$disabledAvgResponsibleProcessingRecordService]);
});

it('loads the disabled page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $enabledAvgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $disabledAvgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => false]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecordServices::class)
        ->set('activeTab', 'disabled')
        ->assertCanSeeTableRecords([$disabledAvgResponsibleProcessingRecordService])
        ->assertCanNotSeeTableRecords([$enabledAvgResponsibleProcessingRecordService]);
});
