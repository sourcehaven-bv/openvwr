<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\WpgProcessingRecordResource;

use App\Filament\Resources\WpgProcessingRecordServiceResource;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\ListWpgProcessingRecordServices;
use App\Models\Wpg\WpgProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

use function it;

it('loads the form', function (): void {
    $organisation = OrganisationTestHelper::create();
    $wpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(WpgProcessingRecordServiceResource::getUrl('edit', ['record' => $wpgProcessingRecordService]))
        ->assertSuccessful();
});

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $wpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListWpgProcessingRecordServices::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords([$wpgProcessingRecordService]);
});

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(WpgProcessingRecordServiceResource::getUrl('create'))
        ->assertSuccessful();
});
