<?php

declare(strict_types=1);

use App\Facades\AuditLog;
use App\Filament\Actions\DetachSharedBulkAction;
use App\Filament\RelationManagers\DocumentRelationManager;
use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Filament\Resources\DocumentTypeResource\Pages\EditDocumentType;
use App\Models\DataBreachRecord;
use App\Models\Document;
use App\Models\DocumentType;
use App\Services\AuditLog\Register\LinksDetachedEvent;
use Filament\Tables\Actions\DeleteBulkAction;

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

it('detaches documents without deleting them and audit logs the removed links', function (): void {
    AuditLog::spy();

    $documents = Document::factory()
        ->count(3)
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->hasAttached($documents)
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(DocumentRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertTableBulkActionDoesNotExist(DeleteBulkAction::class)
        ->callTableBulkAction(DetachSharedBulkAction::class, $documents->take(2))
        ->assertHasNoTableBulkActionErrors();

    expect($dataBreachRecord->documents()->pluck('documents.id')->all())->toEqual([$documents[2]->id])
        ->and(Document::query()->count())->toBe(3);

    AuditLog::shouldHaveReceived('register')
        ->withArgs(static fn (mixed $event): bool => $event instanceof LinksDetachedEvent
            && $event->recordType === $dataBreachRecord->getMorphClass()
            && $event->recordId === $dataBreachRecord->id->toString()
            && $event->relation === 'documents'
            && $event->relatedIds === [$documents[0]->id->toString(), $documents[1]->id->toString()])
        ->once();
});

it('keeps the resource bulk actions for a has-many relation', function (): void {
    $documentType = DocumentType::factory()
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(DocumentRelationManager::class, [
            'ownerRecord' => $documentType,
            'pageClass' => EditDocumentType::class,
        ])
        ->assertTableBulkActionExists(DeleteBulkAction::class)
        ->assertTableBulkActionDoesNotExist(DetachSharedBulkAction::class);
});
