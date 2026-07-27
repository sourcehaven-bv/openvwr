<?php

declare(strict_types=1);

use App\Enums\RegisterLayout;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('wraps the one-page layout on the create page', function (): void {
    $user = UserTestHelper::create(['register_layout' => RegisterLayout::ONE_PAGE]);

    $this->asFilamentUser($user)
        ->get(AvgResponsibleProcessingRecordResource::getUrl('create'))
        ->assertOk()
        ->assertSee('onepage-layout', escape: false);
});

it('wraps the one-page layout on the edit page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation, [
        'register_layout' => RegisterLayout::ONE_PAGE,
    ]);

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentUser($user)
        ->get(AvgResponsibleProcessingRecordResource::getUrl('edit', [
            'record' => $avgResponsibleProcessingRecord,
        ]))
        ->assertOk()
        ->assertSee('onepage-layout', escape: false);
});

it('does not wrap the create page when the stepwise layout is selected', function (): void {
    $user = UserTestHelper::create(['register_layout' => RegisterLayout::STEPS]);

    $this->asFilamentUser($user)
        ->get(AvgResponsibleProcessingRecordResource::getUrl('create'))
        ->assertOk()
        ->assertDontSee('onepage-layout', escape: false);
});
