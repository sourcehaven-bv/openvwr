<?php

declare(strict_types=1);

use App\Filament\RelationManagers\AvgResponsibleProcessingRecordRelationManager;
use App\Filament\Resources\DocumentResource\Pages\EditDocument;
use App\Models\Document;

it('loads the table', function (): void {
    $document = Document::factory()
        ->withAvgResponsibleProcessingRecords()
        ->create();
    $document->refresh();

    $this->asFilamentOrganisationUser($document->organisation)
        ->createLivewireTestable(AvgResponsibleProcessingRecordRelationManager::class, [
            'ownerRecord' => $document,
            'pageClass' => EditDocument::class,
        ])
        ->assertCanSeeTableRecords($document->avgResponsibleProcessingRecords);
});
