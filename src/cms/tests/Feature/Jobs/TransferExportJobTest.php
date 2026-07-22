<?php

declare(strict_types=1);

use App\Components\Uuid\Uuid;
use App\Jobs\TransferExportJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\User;
use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferEntityType;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;

it('exports the records and notifies the user with a download link', function (): void {
    Storage::fake('filament');

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
    Storage::fake('filament');

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
