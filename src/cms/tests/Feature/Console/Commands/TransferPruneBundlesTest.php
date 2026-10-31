<?php

declare(strict_types=1);

use App\Transfer\TransferBundleStorage;
use Illuminate\Support\Facades\Storage;

it('deletes transfer bundles older than the retention period', function (): void {
    $disk = Storage::fake('transfer');
    $expired = now()->subDays(TransferBundleStorage::RETENTION_DAYS)->subMinute()->getTimestamp();

    $disk->put('transfer/exports/old.zip', 'old');
    $disk->put('transfer/imports/old.zip', 'old');
    $disk->put('transfer/exports/new.zip', 'new');
    $disk->put('transfer/imports/new.zip', 'new');
    $disk->put('other/old.zip', 'old');

    touch($disk->path('transfer/exports/old.zip'), $expired);
    touch($disk->path('transfer/imports/old.zip'), $expired);
    touch($disk->path('other/old.zip'), $expired);

    $this->artisan('transfer:prune-bundles')
        ->expectsOutput('2 expired transfer bundles deleted.')
        ->assertSuccessful();

    $disk->assertMissing('transfer/exports/old.zip');
    $disk->assertMissing('transfer/imports/old.zip');
    $disk->assertExists('transfer/exports/new.zip');
    $disk->assertExists('transfer/imports/new.zip');
    $disk->assertExists('other/old.zip');
});

it('is scheduled', function (): void {
    $this->artisan('schedule:test --name transfer:prune-bundles')
        ->assertSuccessful();
});
