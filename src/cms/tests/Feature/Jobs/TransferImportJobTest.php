<?php

declare(strict_types=1);

use App\Events\StaticWebsite\BuildEvent;
use App\Facades\AuditLog;
use App\Jobs\TransferImportJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLog\Transfer\TransferImportEvent;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

it('imports the bundle, deletes it and notifies the user of the result', function (): void {
    Storage::fake('transfer');
    Event::fake([BuildEvent::class]);

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedBundle($sourceOrganisation, $user);

    $job = new TransferImportJob(
        $path,
        $plan,
        $destinationOrganisation->id,
        $user->id,
    );
    app()->call([$job, 'handle']);

    Event::assertDispatched(BuildEvent::class);

    // the uploaded bundle is cleaned up after import
    expect(Storage::disk('transfer')->exists($path))->toBeFalse()
        ->and(AvgResponsibleProcessingRecord::query()->whereBelongsTo($destinationOrganisation)->count())->toBe(1);

    $notification = DatabaseNotification::query()
        ->where('notifiable_id', $user->id->toString())
        ->firstOrFail();

    expect($notification->data['title'])->toBe(__('transfer.import_finished'));
});

it('notifies the user of a failure and still deletes the bundle when the import throws', function (): void {
    Storage::fake('transfer');
    Event::fake([BuildEvent::class]);

    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    // an unreadable (empty) zip on the disk makes the importer throw
    $path = 'transfer/imports/corrupt.zip';
    Storage::disk('transfer')->put($path, 'not a real zip');

    $job = new TransferImportJob(
        $path,
        [],
        $organisation->id,
        $user->id,
    );
    app()->call([$job, 'handle']);

    Event::assertNotDispatched(BuildEvent::class);

    expect(Storage::disk('transfer')->exists($path))->toBeFalse();

    $notification = DatabaseNotification::query()
        ->where('notifiable_id', $user->id->toString())
        ->firstOrFail();

    expect($notification->data['title'])->toBe(__('transfer.import_failed'));
});

it('records an audit event for the import', function (): void {
    Storage::fake('transfer');
    Event::fake([BuildEvent::class]);
    AuditLog::spy();

    $sourceOrganisation = Organisation::factory()->create();
    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedBundle($sourceOrganisation, $user);

    app()->call([new TransferImportJob($path, $plan, $destinationOrganisation->id, $user->id), 'handle']);

    $imported = AvgResponsibleProcessingRecord::query()->whereBelongsTo($destinationOrganisation)->firstOrFail();

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferImportEvent
            && $event->method === TransferImportEvent::METHOD_BUNDLE
            && $event->user?->id->equals($user->id) === true
            && $event->sourceOrganisationId === $sourceOrganisation->id->toString()
            && $event->destinationOrganisationId === $destinationOrganisation->id->toString()
            && $event->failedReason === null
            && in_array([
                'type' => 'avg_responsible_processing_record',
                'id' => $imported->id->toString(),
                'action' => 'created',
            ], $event->result?->records ?? [], true))
        ->once();
});

it('records a failed audit event when the import throws', function (): void {
    Storage::fake('transfer');
    Event::fake([BuildEvent::class]);
    AuditLog::spy();

    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();

    $path = 'transfer/imports/corrupt.zip';
    Storage::disk('transfer')->put($path, 'not a real zip');

    app()->call([new TransferImportJob($path, [], $organisation->id, $user->id), 'handle']);

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferImportEvent
            && $event->method === TransferImportEvent::METHOD_BUNDLE
            && $event->destinationOrganisationId === $organisation->id->toString()
            && $event->result === null
            && $event->failedReason === 'TransferException')
        ->once();
});

it('does not log a source organisation id from the manifest that is not a uuid', function (): void {
    Storage::fake('transfer');
    Event::fake([BuildEvent::class]);
    AuditLog::spy();

    $destinationOrganisation = Organisation::factory()->create();
    $user = User::factory()->create();

    [$path, $plan] = createExportedBundle(Organisation::factory()->create(), $user);

    // the manifest of an uploaded bundle is user-controlled
    $zip = new ZipArchive();
    $zip->open(Storage::disk('transfer')->path($path));
    $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
    $manifest['source_organisation'] = ['id' => str_repeat('x', 5000), 'name' => 'Bron'];
    $zip->addFromString('manifest.json', (string) json_encode($manifest));
    $zip->close();

    app()->call([new TransferImportJob($path, $plan, $destinationOrganisation->id, $user->id), 'handle']);

    AuditLog::shouldHaveReceived('register')
        ->withArgs(fn (mixed $event): bool => $event instanceof TransferImportEvent
            && $event->failedReason === null
            && $event->sourceOrganisationId === null)
        ->once();
});
