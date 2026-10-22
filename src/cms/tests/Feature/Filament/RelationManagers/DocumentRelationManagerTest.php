<?php

declare(strict_types=1);

use App\Filament\RelationManagers\DocumentRelationManager;
use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;
use Tests\Helpers\Model\OrganisationTestHelper;

it('loads the table', function (): void {
    $organisation = OrganisationTestHelper::create();
    $document = Document::factory()->for($organisation)
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()->for($organisation)
        ->hasAttached($document)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(DocumentRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertCanSeeTableRecords([$document]);
});
