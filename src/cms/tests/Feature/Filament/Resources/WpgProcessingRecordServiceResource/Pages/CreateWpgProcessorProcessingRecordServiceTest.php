<?php

declare(strict_types=1);

use App\Enums\CoreEntityDataCollectionSource;
use App\Filament\Resources\WpgProcessingRecordResource\Pages\CreateWpgProcessingRecord;
use App\Filament\Resources\WpgProcessingRecordServiceResource;
use App\Filament\Resources\WpgProcessingRecordServiceResource\Pages\CreateWpgProcessingRecordService;
use App\Models\Tag;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the create page', function (): void {
    $this->asFilamentUser()
        ->get(WpgProcessingRecordServiceResource::getUrl('create'))
        ->assertSuccessful();
});

it('can create an entry', function (): void {
    $name = fake()->uuid();

    $this->asFilamentUser()
        ->createLivewireTestable(CreateWpgProcessingRecordService::class)
        ->fillForm([
            'name' => $name,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(WpgProcessingRecordService::class, [
        'name' => $name,
    ]);
});


it('can create an entry with a new tag', function (): void {
    $organisation = OrganisationTestHelper::create();
    $wpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create([
            'enabled' => true,
        ]);

    $name = fake()->uuid();
    $tagName = fake()->uuid();

    $this->assertDatabaseEmpty('taggables');

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateWpgProcessingRecord::class)
        ->fillForm([
            'data_collection_source' => CoreEntityDataCollectionSource::PRIMARY->value,
            'name' => $name,
            'wpg_processing_record_service_id' => $wpgProcessingRecordService->id->toString(),
        ])
        ->call('mountFormComponentAction', 'data.tags', 'createOption')
        ->set('mountedFormComponentActionsData.0.name', $tagName)
        ->call('callMountedFormComponentAction')
        ->assertHasNoFormErrors()
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(WpgProcessingRecord::class, [
        'name' => $name,
        'organisation_id' => $organisation->id,
    ]);
    $this->assertDatabaseHas(Tag::class, [
        'name' => $tagName,
        'organisation_id' => $organisation->id,
    ]);
    $this->assertDatabaseHas('taggables', [
        'taggable_type' => WpgProcessingRecord::class,
    ]);
});
