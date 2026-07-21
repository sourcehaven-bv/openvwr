<?php

declare(strict_types=1);

use App\Filament\Resources\WpgProcessingRecordResource\Pages\EditWpgProcessingRecord;
use App\Filament\Resources\WpgProcessingRecordServiceResource;
use App\Models\Tag;
use App\Models\Wpg\WpgProcessingRecord;
use App\Models\Wpg\WpgProcessingRecordService;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the edit page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $wpgProcessingRecordService = WpgProcessingRecordService::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(WpgProcessingRecordServiceResource::getUrl('edit', ['record' => $wpgProcessingRecordService]))
        ->assertSuccessful();
});

it('can be attached to a tag', function (): void {
    $organisation = OrganisationTestHelper::create();
    $WpgProcessingRecord = WpgProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();
    $tag = Tag::factory()
        ->recycle($organisation)
        ->create();

    expect($WpgProcessingRecord->tags->count())
        ->toBe(0);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditWpgProcessingRecord::class, [
            'record' => $WpgProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'tags' => [$tag->id->toString()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $WpgProcessingRecord->refresh();
    expect($WpgProcessingRecord->tags->count())
        ->toBe(1);
});
