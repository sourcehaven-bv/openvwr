<?php

declare(strict_types=1);

use App\Events\StaticWebsite\BuildEvent;
use App\Jobs\TransferImportJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\User;
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
