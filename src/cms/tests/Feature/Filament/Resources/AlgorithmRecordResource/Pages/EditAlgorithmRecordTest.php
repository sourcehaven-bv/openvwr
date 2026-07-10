<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\AlgorithmRecordResource;
use App\Filament\Resources\AlgorithmRecordResource\Pages\EditAlgorithmRecord;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\EntityNumber;
use App\Services\EntityNumberService;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the edit page with all layouts', function (RegisterLayout $registerLayout): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, ['register_layout' => $registerLayout]);

    $algorithmRecord = AlgorithmRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->get(AlgorithmRecordResource::getUrl('edit', [
            'record' => $algorithmRecord,
        ]))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

it('can be saved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $algorithmRecord = AlgorithmRecord::factory()
        ->recycle($organisation)
        ->create();
    $name = fake()->word();
    $metaDateOfDevelopment = fake()->date();
    $metaOwnerAlgorithm = fake()->name();
    $metaProductOwnerAlgorithm = fake()->name();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->fillForm([
            'name' => $name,
            'meta_date_of_development' => $metaDateOfDevelopment,
            'meta_owner_algorithm' => $metaOwnerAlgorithm,
            'meta_product_owner_algorithm' => $metaProductOwnerAlgorithm,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $algorithmRecord->refresh();
    expect($algorithmRecord->name)
        ->toBe($name)
        ->and($algorithmRecord->meta_date_of_development?->toDateString())->toBe($metaDateOfDevelopment)
        ->and($algorithmRecord->meta_owner_algorithm)->toBe($metaOwnerAlgorithm)
        ->and($algorithmRecord->meta_product_owner_algorithm)->toBe($metaProductOwnerAlgorithm);
});

it('can be cloned', function (): void {
    $organisation = OrganisationTestHelper::create();
    $algorithmRecord = AlgorithmRecord::factory()
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
        ->createLivewireTestable(EditAlgorithmRecord::class, [
            'record' => $algorithmRecord->getRouteKey(),
        ])
        ->callAction('clone')
        ->assertRedirect();

    $algorithmRecordClone = AlgorithmRecord::query()
        ->where('entity_number_id', $entityNumber->id)
        ->firstOrFail();

    expect($algorithmRecordClone->entity_number_id)->not()->toBe($algorithmRecord->entity_number_id)
        ->and($algorithmRecordClone->name)->toBe($algorithmRecord->name)
        ->and($algorithmRecordClone->public_from?->toJson())->toBe($algorithmRecord->public_from?->toJson())
        ->and($algorithmRecordClone->algorithm_theme_id)->toEqual($algorithmRecord->algorithm_theme_id)
        ->and($algorithmRecordClone->algorithm_status_id)->toEqual($algorithmRecord->algorithm_status_id)
        ->and($algorithmRecordClone->algorithm_publication_category_id)->toEqual($algorithmRecord->algorithm_publication_category_id)
        ->and($algorithmRecordClone->algorithm_meta_schema_id)->toEqual($algorithmRecord->algorithm_meta_schema_id);

    expect($algorithmRecordClone->fgRemark)->toBeNull();
    expect($algorithmRecordClone->snapshots)->toBeEmpty();

    expect($algorithmRecordClone->documents->pluck('id')->toArray())
        ->toBe($algorithmRecordClone->documents->pluck('id')->toArray());
});
