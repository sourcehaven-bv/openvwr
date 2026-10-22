<?php

declare(strict_types=1);

use App\Filament\RelationManagers\ResponsibleRelationManager;
use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Models\DataBreachRecord;
use App\Models\Responsible;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the table', function (): void {
    $organisation = OrganisationTestHelper::create();
    $responsible = Responsible::factory()->for($organisation)
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()->for($organisation)
        ->hasAttached($responsible)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(ResponsibleRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertCanSeeTableRecords([$responsible]);
});
