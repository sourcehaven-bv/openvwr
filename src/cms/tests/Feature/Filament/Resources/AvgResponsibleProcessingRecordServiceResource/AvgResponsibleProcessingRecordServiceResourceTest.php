<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources\AvgProcessorProcessingRecordResource;

use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource;
use App\Filament\Resources\AvgResponsibleProcessingRecordServiceResource\Pages\ListAvgResponsibleProcessingRecordServices;
use App\Models\Avg\AvgResponsibleProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

use function it;

it('loads the form', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(AvgResponsibleProcessingRecordServiceResource::getUrl('edit', [
            'record' => $avgResponsibleProcessingRecordService,
        ]))
        ->assertSuccessful();
});

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecordService = AvgResponsibleProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListAvgResponsibleProcessingRecordServices::class)
        ->set('activeTab', 'all')
        ->assertCanSeeTableRecords([$avgResponsibleProcessingRecordService]);
});

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(AvgResponsibleProcessingRecordServiceResource::getUrl('create'))
        ->assertSuccessful();
});
