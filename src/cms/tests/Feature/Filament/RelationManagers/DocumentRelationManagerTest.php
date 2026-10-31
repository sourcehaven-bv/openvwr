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
use Filament\Actions\DeleteBulkAction;
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

it('detaches documents without deleting them and audit logs the removed links', function (): void {
    AuditLog::spy();

    $organisation = OrganisationTestHelper::create();
    $documents = Document::factory()->for($organisation)
        ->count(3)
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()->for($organisation)
        ->hasAttached($documents)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(DocumentRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertTableBulkActionDoesNotExist(DeleteBulkAction::class)
        ->callTableBulkAction(DetachSharedBulkAction::class, $documents->take(2))
        ->assertHasNoTableBulkActionErrors();

    expect($dataBreachRecord->documents()->pluck('documents.id')->all())->toEqual([$documents[2]->id])
        ->and(Document::query()->count())->toBe(3);

    $detachedIds = null;
    AuditLog::shouldHaveReceived('register')
        ->withArgs(static function (mixed $event) use ($dataBreachRecord, &$detachedIds): bool {
            if (
                !$event instanceof LinksDetachedEvent
                || $event->recordType !== $dataBreachRecord->getMorphClass()
                || $event->recordId !== $dataBreachRecord->id->toString()
                || $event->relation !== 'documents'
            ) {
                return false;
            }

            $detachedIds = $event->relatedIds;

            return true;
        })
        ->once();

    // The table sort decides the order of the selected records.
    expect($detachedIds)->toEqualCanonicalizing([$documents[0]->id->toString(), $documents[1]->id->toString()]);
});

it('keeps the resource bulk actions for a has-many relation', function (): void {
    $organisation = OrganisationTestHelper::create();
    $documentType = DocumentType::factory()->for($organisation)
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(DocumentRelationManager::class, [
            'ownerRecord' => $documentType,
            'pageClass' => EditDocumentType::class,
        ])
        ->assertTableBulkActionExists(DeleteBulkAction::class)
        ->assertTableBulkActionDoesNotExist(DetachSharedBulkAction::class);
});
