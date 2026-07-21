<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\AvgProcessorProcessingRecordResource;

use App\Filament\Resources\AvgProcessorProcessingRecordServiceResource;
use App\Filament\Resources\AvgProcessorProcessingRecordServiceResource\Pages\ListAvgProcessorProcessingRecordServices;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

use function it;

it('loads the form', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(AvgProcessorProcessingRecordServiceResource::getUrl('edit', [
            'record' => $avgProcessorProcessingRecordService,
        ]))
        ->assertSuccessful();
});

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgProcessorProcessingRecordServices::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords([$avgProcessorProcessingRecordService]);
});

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(AvgProcessorProcessingRecordServiceResource::getUrl('create'))
        ->assertSuccessful();
});
