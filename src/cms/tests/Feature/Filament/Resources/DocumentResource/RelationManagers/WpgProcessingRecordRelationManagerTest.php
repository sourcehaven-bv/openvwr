<?php

declare(strict_types=1);

use App\Filament\RelationManagers\WpgProcessingRecordRelationManager;
use App\Filament\Resources\DocumentResource\Pages\EditDocument;
use App\Models\Document;

it('loads the table', function (): void {
    $document = Document::factory()
        ->withWpgProcessingRecords()
        ->create();
    $document->refresh();

    $this->asFilamentOrganisationUser($document->organisation)
        ->createLivewireTestable(WpgProcessingRecordRelationManager::class, [
            'ownerRecord' => $document,
            'pageClass' => EditDocument::class,
        ])
        ->assertCanSeeTableRecords($document->wpgProcessingRecords);
});
