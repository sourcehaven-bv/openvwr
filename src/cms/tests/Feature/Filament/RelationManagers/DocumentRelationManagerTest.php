<?php

declare(strict_types=1);

use App\Filament\RelationManagers\DocumentRelationManager;
use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Models\DataBreachRecord;
use App\Models\Document;

it('loads the table', function (): void {
    $document = Document::factory()
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->hasAttached($document)
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(DocumentRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertCanSeeTableRecords([$document]);
});
