<?php

declare(strict_types=1);

use App\Facades\AuditLog;
use App\Filament\Actions\DetachSharedBulkAction;
use App\Filament\RelationManagers\ResponsibleRelationManager;
use App\Filament\Resources\DataBreachRecord\Pages\EditDataBreachRecord;
use App\Models\DataBreachRecord;
use App\Models\Responsible;
use App\Services\AuditLog\Register\LinksDetachedEvent;
use Filament\Tables\Actions\DeleteBulkAction;

it('loads the table', function (): void {
    $responsible = Responsible::factory()
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->hasAttached($responsible)
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(ResponsibleRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertCanSeeTableRecords([$responsible]);
});

it('detaches responsibles without deleting them and audit logs the removed links', function (): void {
    AuditLog::spy();

    $responsible = Responsible::factory()
        ->create();
    $dataBreachRecord = DataBreachRecord::factory()
        ->hasAttached($responsible)
        ->create();

    $this->asFilamentUser()
        ->createLivewireTestable(ResponsibleRelationManager::class, [
            'ownerRecord' => $dataBreachRecord,
            'pageClass' => EditDataBreachRecord::class,
        ])
        ->assertTableBulkActionDoesNotExist(DeleteBulkAction::class)
        ->callTableBulkAction(DetachSharedBulkAction::class, [$responsible])
        ->assertHasNoTableBulkActionErrors();

    expect($dataBreachRecord->responsibles()->count())->toBe(0)
        ->and(Responsible::query()->whereKey($responsible->id)->exists())->toBeTrue();

    AuditLog::shouldHaveReceived('register')
        ->withArgs(static fn (mixed $event): bool => $event instanceof LinksDetachedEvent
            && $event->organisationId === $dataBreachRecord->getOrganisation()->id->toString()
            && $event->relation === 'responsibles'
            && $event->relatedIds === [$responsible->id->toString()])
        ->once();
});
