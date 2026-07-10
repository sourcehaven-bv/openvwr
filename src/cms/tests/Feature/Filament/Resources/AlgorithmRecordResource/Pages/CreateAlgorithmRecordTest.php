<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\AlgorithmRecordResource;
use App\Filament\Resources\AlgorithmRecordResource\Pages\CreateAlgorithmRecord;
use App\Models\Algorithm\AlgorithmMetaSchema;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmRecord;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the create page with all layouts', function (RegisterLayout $registerLayout): void {
    $user = UserTestHelper::create(['register_layout' => $registerLayout]);

    $this->asFilamentUser($user)
        ->get(AlgorithmRecordResource::getUrl('create'))
        ->assertSuccessful();
})->with(RegisterLayout::cases());

it('can create an entry', function (): void {
    $organisation = OrganisationTestHelper::create();
    $algorithmMetaSchema = AlgorithmMetaSchema::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $algorithmPublicationCategory = AlgorithmPublicationCategory::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $algorithmStatus = AlgorithmStatus::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $algorithmTheme = AlgorithmTheme::factory()
        ->recycle($organisation)
        ->create(['enabled' => true]);
    $name = fake()->uuid();
    $metaDateOfDevelopment = fake()->date();
    $metaOwnerAlgorithm = fake()->name();
    $metaProductOwnerAlgorithm = fake()->name();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAlgorithmRecord::class)
        ->fillForm([
            'name' => $name,
            'algorithm_meta_schema_id' => $algorithmMetaSchema->id,
            'algorithm_publication_category_id' => $algorithmPublicationCategory->id,
            'algorithm_status_id' => $algorithmStatus->id,
            'algorithm_theme_id' => $algorithmTheme->id,
            'meta_date_of_development' => $metaDateOfDevelopment,
            'meta_owner_algorithm' => $metaOwnerAlgorithm,
            'meta_product_owner_algorithm' => $metaProductOwnerAlgorithm,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(AlgorithmRecord::class, [
        'name' => $name,
        'meta_date_of_development' => $metaDateOfDevelopment,
        'meta_owner_algorithm' => $metaOwnerAlgorithm,
        'meta_product_owner_algorithm' => $metaProductOwnerAlgorithm,
    ]);
});
