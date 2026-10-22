<?php

declare(strict_types=1);

use App\Components\Uuid\Uuid;
use App\Facades\AuditLog;
use App\Jobs\TransferExportJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLog\Transfer\TransferExportEvent;
use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferEntityType;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;

it('exports the records and notifies the user with a download link', function (): void {
    Storage::fake('transfer');

    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Verwerking']);

    $job = new TransferExportJob(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        $organisation->id,
        $user->id,
    );
    $job->handle(app(BundleExporter::class));

    $notification = DatabaseNotification::query()
        ->where('notifiable_id', $user->id->toString())
        ->firstOrFail();

    expect($notification->data['title'])->toBe(__('transfer.export_ready'))
        ->and($notification->data['actions'][0]['url'])->toContain('/transfer-export/');
});

it('does nothing more than exporting when the user no longer exists', function (): void {
    Storage::fake('transfer');

    $organisation = Organisation::factory()->create();
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Verwerking']);

    $job = new TransferExportJob(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        $organisation->id,
        Uuid::generate(),
    );
    $job->handle(app(BundleExporter::class));

    expect(DatabaseNotification::query()->count())->toBe(0);
});

it('records an audit event for the export', function (): void {
    Storage::fake('transfer');
    AuditLog::spy();

    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Verwerking']);

    $job = new TransferExportJob(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        ['processors' => []],
        $organisation->id,
        $user->id,
    );
    $job->handle(app(BundleExporter::class));

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferExportEvent
            && $event->user?->id->equals($user->id) === true
            && $event->sourceOrganisationId === $organisation->id->toString()
            && $event->recordType === TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD
            && $event->recordIds === [$record->id->toString()]
            && $event->selectedRelated === ['processors' => []]
            && $event->failedReason === null)
        ->once();
});

it('records a failed audit event when the export job fails', function (): void {
    AuditLog::spy();

    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    $job = new TransferExportJob(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [],
        [],
        $organisation->id,
        $user->id,
    );
    $job->failed(new RuntimeException('disk full'));

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferExportEvent
            && $event->user?->id->equals($user->id) === true
            && $event->failedReason === 'RuntimeException')
        ->once();
});
