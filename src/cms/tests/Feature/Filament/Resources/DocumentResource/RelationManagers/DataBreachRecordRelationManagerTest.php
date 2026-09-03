<?php

declare(strict_types=1);

use App\Filament\RelationManagers\DataBreachRecordRelationManager;
use App\Filament\Resources\DocumentResource\Pages\EditDocument;
use App\Models\Document;

it('loads the table', function (): void {
    $document = Document::factory()
        ->withDataBreachRecords()
        ->create();
    $document->refresh();

    $this->asFilamentOrganisationUser($document->organisation)
        ->createLivewireTestable(DataBreachRecordRelationManager::class, [
            'ownerRecord' => $document,
            'pageClass' => EditDocument::class,
        ])
        ->assertCanSeeTableRecords($document->dataBreachRecords);
});
