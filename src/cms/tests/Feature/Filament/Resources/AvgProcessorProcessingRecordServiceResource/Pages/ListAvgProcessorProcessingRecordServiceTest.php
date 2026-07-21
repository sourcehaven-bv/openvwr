<?php

declare(strict_types=1);

use App\Filament\Resources\AvgProcessorProcessingRecordServiceResource\Pages\ListAvgProcessorProcessingRecordServices;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgProcessorProcessingRecordServices::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($avgProcessorProcessingRecordService);
});

it('loads the enabled page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $enabledAvgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $disabledAvgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => false]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgProcessorProcessingRecordServices::class)
        ->set('activeTab', 'enabled')
        ->assertCanSeeTableRecords([$enabledAvgProcessorProcessingRecordService])
        ->assertCanNotSeeTableRecords([$disabledAvgProcessorProcessingRecordService]);
});

it('loads the disabled page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $enabledAvgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $disabledAvgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => false]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgProcessorProcessingRecordServices::class)
        ->set('activeTab', 'disabled')
        ->assertCanSeeTableRecords([$disabledAvgProcessorProcessingRecordService])
        ->assertCanNotSeeTableRecords([$enabledAvgProcessorProcessingRecordService]);
});
