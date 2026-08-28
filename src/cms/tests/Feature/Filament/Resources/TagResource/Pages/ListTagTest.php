<?php

declare(strict_types=1);

use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\System;
use App\Models\Tag;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the list page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $tags = Tag::factory()
        ->recycle($organisation)
        ->count(5)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListTags::class)
        ->assertCanSeeTableRecords($tags);
});

it('shows the number of linked items', function (): void {
    $organisation = OrganisationTestHelper::create();
    $tag = Tag::factory()->for($organisation)->create();
    $processingRecord = AvgResponsibleProcessingRecord::factory()->for($organisation)->create();
    $system = System::factory()->for($organisation)->create();

    $tag->avgResponsibleProcessingRecords()->attach($processingRecord);
    $tag->systems()->attach($system);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ListTags::class)
        ->assertTableColumnStateSet('items_count', 2, $tag->getKey());
});
