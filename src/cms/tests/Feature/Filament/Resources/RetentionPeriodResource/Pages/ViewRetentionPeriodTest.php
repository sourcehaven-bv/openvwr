<?php

declare(strict_types=1);

use App\Filament\Resources\RetentionPeriodResource;
use App\Models\RetentionPeriod;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the view page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $retentionPeriod = RetentionPeriod::factory()
        ->recycle($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(RetentionPeriodResource::getUrl('view', ['record' => $retentionPeriod]))
        ->assertSuccessful();
});
