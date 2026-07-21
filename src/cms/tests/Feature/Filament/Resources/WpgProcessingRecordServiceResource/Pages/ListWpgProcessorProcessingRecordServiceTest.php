<?php

declare(strict_types=1);

use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\ListWpgProcessingRecordServices;
use App\Models\Wpg\WpgProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $wpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListWpgProcessingRecordServices::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords($wpgProcessingRecordService);
});

it('loads the enabled page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $enabledWpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $disabledWpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => false]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListWpgProcessingRecordServices::class)
        ->set('activeTab', 'enabled')
        ->assertCanSeeTableRecords([$enabledWpgProcessingRecordService])
        ->assertCanNotSeeTableRecords([$disabledWpgProcessingRecordService]);
});

it('loads the disabled page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $enabledWpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $disabledWpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create(['enabled' => false]);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListWpgProcessingRecordServices::class)
        ->set('activeTab', 'disabled')
        ->assertCanSeeTableRecords([$disabledWpgProcessingRecordService])
        ->assertCanNotSeeTableRecords([$enabledWpgProcessingRecordService]);
});
