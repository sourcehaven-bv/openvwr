<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\AvgProcessorProcessingRecordResource;
use App\Filament\Resources\AvgProcessorProcessingRecordResource\Pages\EditAvgProcessorProcessingRecord;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\EntityNumber;
use App\Models\Tag;
use App\Services\EntityNumberService;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the edit page with all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);

    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->get(AvgProcessorProcessingRecordResource::getUrl('edit', [
            'record' => $avgProcessorProcessingRecord,
        ]))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

it('can be saved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();
    $name = fake()->uuid();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgProcessorProcessingRecord::class, [
            'record' => $avgProcessorProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'name' => $name,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgProcessorProcessingRecord->refresh();
    expect($avgProcessorProcessingRecord->name)
        ->toBe($name);
});

it('can be cloned', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->recycle($organisation)
        ->withAllRelatedEntities()
        ->create();
    $entityNumber = EntityNumber::factory()
        ->create();

    $this->mock(EntityNumberService::class)
        ->shouldReceive('generate')
        ->once()
        ->andReturn($entityNumber);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgProcessorProcessingRecord::class, [
            'record' => $avgProcessorProcessingRecord->getRouteKey(),
        ])
        ->callAction('clone')
        ->assertRedirect();

    $avgProcessorProcessingRecordClone = AvgProcessorProcessingRecord::query()
        ->where('entity_number_id', $entityNumber->id)
        ->firstOrFail();

    expect($avgProcessorProcessingRecordClone->entity_number_id)->not()->toBe($avgProcessorProcessingRecord->entity_number_id)
        ->and($avgProcessorProcessingRecordClone->name)->toBe($avgProcessorProcessingRecord->name)
        ->and($avgProcessorProcessingRecordClone->public_from?->toJson())->toBe($avgProcessorProcessingRecord->public_from?->toJson());

    expect($avgProcessorProcessingRecordClone->fgRemark)->toBeNull();
    expect($avgProcessorProcessingRecordClone->snapshots)->toBeEmpty();

    expect($avgProcessorProcessingRecordClone->avgGoals->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->avgGoals->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->contactPersons->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->contactPersons->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->dataBreachRecords->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->dataBreachRecords->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->documents->pluck('id'))
        ->toEqual($avgProcessorProcessingRecord->documents->pluck('id'));

    expect($avgProcessorProcessingRecordClone->processors->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->processors->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->receivers->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->receivers->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->remarks->pluck('body')->toArray())
        ->toBe($avgProcessorProcessingRecord->remarks->pluck('body')->toArray());

    expect($avgProcessorProcessingRecordClone->responsibles->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->responsibles->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->stakeholders->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->stakeholders->pluck('id')->toArray());

    expect($avgProcessorProcessingRecordClone->systems->pluck('id')->toArray())
        ->toEqual($avgProcessorProcessingRecord->systems->pluck('id')->toArray());
});


it('can be attached to a tag', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $tag = Tag::factory()
        ->recycle($organisation)
        ->create();

    expect($avgProcessorProcessingRecord->tags->count())
        ->toBe(0);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgProcessorProcessingRecord::class, [
            'record' => $avgProcessorProcessingRecord->getRouteKey(),
        ])
        ->fillForm([
            'tags' => [$tag->id->toString()],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgProcessorProcessingRecord->refresh();
    expect($avgProcessorProcessingRecord->tags->count())
        ->toBe(1);
});
