<?php

declare(strict_types=1);

use App\Enums\Authorization\Permission;
use App\Filament\Pages\TransferImport;
use App\Jobs\TransferImportJob;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Services\Virusscanner\FakeVirusscanner;
use App\Services\Virusscanner\Virusscanner;
use App\Transfer\Export\BundleExporter;
use App\Transfer\TransferEntityType;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

/**
 * Build a transfer zip for the given organisation and stage it as an uploaded file
 * the import form can accept.
 */
function stageTransferUpload(Organisation $organisation): File
{
    $record = AvgResponsibleProcessingRecord::factory()->for($organisation)->create(['name' => 'Verwerking']);

    $path = app(BundleExporter::class)->export(
        TransferEntityType::AVG_RESPONSIBLE_PROCESSING_RECORD,
        [$record->id->toString()],
        [],
        $organisation,
    );

    $bytes = Storage::disk('transfer')->get($path);

    return TemporaryUploadedFile::fake()->createWithContent('bundle.zip', $bytes);
}

it('loads the import page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(TransferImport::getUrl(tenant: $organisation))
        ->assertSee(__('transfer.import_help'));
});

it('cannot access the import page without the transfer import permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // the legacy import permission must not grant access to the transfer import
    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::CORE_ENTITY_IMPORT])
        ->get(TransferImport::getUrl(tenant: $organisation))
        ->assertForbidden();
});

it('can access the import page with the transfer import permission', function (): void {
    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::TRANSFER_IMPORT])
        ->get(TransferImport::getUrl(tenant: $organisation))
        ->assertOk();
});

it('does not allow the client to tamper with the server-set bundle path', function (): void {
    $this->asFilamentUser();

    // bundlePath is #[Locked]; a client attempting to point it at another
    // organisation's export must be rejected outright.
    expect(fn () => $this->createLivewireTestable(TransferImport::class)
        ->set('bundlePath', 'transfer/exports/someone-else.zip'))
        ->toThrow(CannotUpdateLockedPropertyException::class);
});

it('does not import when no bundle has been analysed', function (): void {
    Bus::fake();

    $this->asFilamentUser()
        ->createLivewireTestable(TransferImport::class)
        ->call('cancel');

    Bus::assertNotDispatched(TransferImportJob::class);
});

it('analyses a valid bundle, imports it and resets the form', function (): void {
    Storage::fake('transfer');
    Bus::fake();
    $this->app->bind(Virusscanner::class, FakeVirusscanner::class);

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $upload = stageTransferUpload($organisation);

    $component = $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::TRANSFER_IMPORT])
        ->createLivewireTestable(TransferImport::class)
        ->set('files', [$upload])
        ->call('analyse')
        ->assertSet('sourceOrganisation', $organisation->name);

    expect($component->get('items'))->not->toBeEmpty()
        ->and($component->get('bundlePath'))->not->toBeNull();

    $component->call('import');

    Bus::assertDispatched(TransferImportJob::class);
    $component->assertSet('bundlePath', null)
        ->assertSet('items', []);
});

it('rejects an invalid bundle and cleans up the uploaded file', function (): void {
    Storage::fake('transfer');
    $this->app->bind(Virusscanner::class, FakeVirusscanner::class);

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    // stage a non-zip file so the bundle reader throws
    $upload = TemporaryUploadedFile::fake()->createWithContent('bundle.zip', 'not a zip');

    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::TRANSFER_IMPORT])
        ->createLivewireTestable(TransferImport::class)
        ->set('files', [$upload])
        ->call('analyse')
        ->assertSet('bundlePath', null)
        ->assertNotified();
});

it('analyses a bundle whose manifest has no exported at timestamp', function (): void {
    Storage::fake('transfer');
    $this->app->bind(Virusscanner::class, FakeVirusscanner::class);

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $entityId = fake()->uuid();
    $entity = [
        'type' => 'tag',
        'id' => $entityId,
        'origin_id' => $entityId,
        'name' => 'Label',
        'attributes' => ['name' => 'Label'],
    ];

    $zipPath = sprintf('%s/%s.zip', sys_get_temp_dir(), fake()->uuid());
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString(sprintf('entities/tag/%s.json', $entityId), json_encode($entity, JSON_THROW_ON_ERROR));
    // manifest without an exported_at key
    $zip->addFromString('manifest.json', json_encode([
        'format' => 'openvwr-transfer',
        'version' => 1,
    ], JSON_THROW_ON_ERROR));
    $zip->close();

    $upload = TemporaryUploadedFile::fake()->createWithContent('bundle.zip', (string) file_get_contents($zipPath));

    $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::TRANSFER_IMPORT])
        ->createLivewireTestable(TransferImport::class)
        ->set('files', [$upload])
        ->call('analyse')
        ->assertSet('exportedAt', null)
        ->assertSet('sourceOrganisation', '');
});

it('cancels an analysed bundle and deletes the uploaded file', function (): void {
    Storage::fake('transfer');
    $this->app->bind(Virusscanner::class, FakeVirusscanner::class);

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $upload = stageTransferUpload($organisation);

    $component = $this->withFilamentSession($user, $organisation)
        ->withPermissions($user, [Permission::TRANSFER_IMPORT])
        ->createLivewireTestable(TransferImport::class)
        ->set('files', [$upload])
        ->call('analyse');

    $bundlePath = $component->get('bundlePath');
    expect(Storage::disk(TransferImport::DISK)->exists($bundlePath))->toBeTrue();

    $component->call('cancel')
        ->assertSet('bundlePath', null);

    expect(Storage::disk(TransferImport::DISK)->exists($bundlePath))->toBeFalse();
});
