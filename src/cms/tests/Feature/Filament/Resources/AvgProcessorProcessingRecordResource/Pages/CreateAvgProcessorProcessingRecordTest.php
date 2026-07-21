<?php

declare(strict_types=1);

use App\Enums\CoreEntityDataCollectionSource;
use App\Enums\RegisterLayout;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages\CreateAvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecordService;
use App\Models\Responsible;
use App\Models\Tag;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the create page with all layouts', function (RegisterLayout $registerLayout): void {
    $user = UserTestHelper::create(['register_layout' => $registerLayout]);

    $this->asFilamentUser($user)
        ->get(AvgProcessorProcessingRecordResource::getUrl('create'))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

it('can create an entry', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create([
            'enabled' => true,
        ]);
    $responsible = Responsible::factory()
        ->recycle($organisation)
        ->create();
    $name = fake()->uuid();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgProcessorProcessingRecord::class)
        ->fillForm([
            'data_collection_source' => CoreEntityDataCollectionSource::PRIMARY->value,
            'name' => $name,
            'avg_processor_processing_record_service_id' => $avgProcessorProcessingRecordService->id,
            'responsible_id' => [$responsible->id->toString()],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(AvgProcessorProcessingRecord::class, [
        'name' => $name,
    ]);
});

it('does not allow an invalid value for dataCollectionSource', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create([
            'enabled' => true,
        ]);
    $name = fake()->uuid();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgProcessorProcessingRecord::class)
        ->fillForm([
            'data_collection_source' => 'invalid',
            'name' => $name,
            'avg_processor_processing_record_service_id' => $avgProcessorProcessingRecordService->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['data_collection_source']);
});

it('can create an entry with a new tag', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecordService = AvgProcessorProcessingRecordService::factory()
        ->recycle($organisation)
        ->create([
            'enabled' => true,
        ]);
    $responsible = Responsible::factory()
        ->recycle($organisation)
        ->create();
    $name = fake()->uuid();
    $tagName = fake()->uuid();

    $this->assertDatabaseEmpty('taggables');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgProcessorProcessingRecord::class)
        ->fillForm([
            'data_collection_source' => CoreEntityDataCollectionSource::PRIMARY->value,
            'name' => $name,
            'avg_processor_processing_record_service_id' => $avgProcessorProcessingRecordService->id->toString(),
            'responsible_id' => [$responsible->id->toString()],
        ])
        ->call('mountFormComponentAction', 'data.tags', 'createOption')
        ->set('mountedFormComponentActionsData.0.name', $tagName)
        ->call('callMountedFormComponentAction')
        ->assertHasNoFormErrors()
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(AvgProcessorProcessingRecord::class, [
        'name' => $name,
        'organisation_id' => $organisation->id,
    ]);
    $this->assertDatabaseHas(Tag::class, [
        'name' => $tagName,
        'organisation_id' => $organisation->id,
    ]);
    $this->assertDatabaseHas('taggables', [
        'taggable_type' => AvgProcessorProcessingRecord::class,
    ]);
});
