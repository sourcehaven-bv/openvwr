<?php

declare(strict_types=1);

use App\Filament\Pages\Import;
use App\Import\Factories\Avg\AvgResponsibleProcessingRecordFactory;
use App\Import\ImportFailedException;
use App\Import\ZipImporter;
use App\Models\User;
use App\Services\BuildContextService;
use Filament\Notifications\DatabaseNotification as FilamentDatabaseNotification;
use Filament\Notifications\Notification;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

it('loads the import page', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(Import::getUrl(tenant: $organisation))
        ->assertSee(__('import.help'));
});

it('validates files input', function (): void {
    $this->asFilamentUser()
        ->createLivewireTestable(Import::class)
        ->fillForm([
            'files' => [],
        ])
        ->call('submit')
        ->assertHasFormErrors(['files' => 'required']);
});

it('can submit the files and sends notification', function (): void {
    $this->asFilamentUser();

    $filename = sprintf('%s.zip', fake()->slug());
    $importFile = new TemporaryUploadedFile($filename, '');

    $buildContextService = $this->app->get(BuildContextService::class);

    $zipImporter = $this->mock(ZipImporter::class)
        ->shouldReceive('importFiles')
        ->once()
        ->withSomeOfArgs([$importFile])
        ->getMock();

    prepareImportZip($filename);

    $importPage = new Import();
    $importPage->files = [$importFile];
    $importPage->submit($buildContextService, $zipImporter);

    Notification::assertNotified(__('import.upload_success'));
});

it('can submit the files and notifies with escaped filename', function (): void {
    $user = UserTestHelper::create();
    $this->asFilamentUser($user);
    ConfigTestHelper::set('import.factories', ['foo' => AvgResponsibleProcessingRecordFactory::class]);

    $filename = sprintf('%s.zip', fake()->slug());
    $tempZipSourcePath = sprintf('%s/%s', sys_get_temp_dir(), $filename);

    $zipFile = new ZipArchive();
    $zipFile->open($tempZipSourcePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zipFile->addFromString('foo/"<img src=x onerror=alert()>`WOOF.json', json_encode([['foo' => 'foo content']]));
    $zipFile->close();

    /** @var FilesystemAdapter $storage */
    $storage = Storage::disk(ConfigTestHelper::get('filesystems.default'));
    $storage->put(sprintf('livewire-tmp/%s', $filename), file_get_contents($tempZipSourcePath));

    $this->assertDatabaseEmpty(DatabaseNotification::class);

    $importPage = new Import();
    $importPage->files = [new TemporaryUploadedFile($filename, '')];
    $importPage->submit($this->app->get(BuildContextService::class), $this->app->get(ZipImporter::class));

    $this->assertDatabaseCount(DatabaseNotification::class, 1);

    $databaseNotification = DatabaseNotification::first();
    $this->assertEquals(FilamentDatabaseNotification::class, $databaseNotification->type);
    $this->assertEquals(User::class, $databaseNotification->notifiable_type);
    $this->assertEquals($user->id, $databaseNotification->notifiable_id);
    $this->assertEquals($databaseNotification->data['body'], 'foo/&quot;&lt;img src=x onerror=alert()&gt;`WOOF.json');
});

it('shows import failed notification on failure', function (): void {
    $this->asFilamentUser();

    $filename = sprintf('%s.zip', fake()->slug());
    $importFile = new TemporaryUploadedFile($filename, '');

    $buildContextService = $this->app->get(BuildContextService::class);

    $zipImporter = $this->mock(ZipImporter::class)
        ->shouldReceive('importFiles')
        ->once()
        ->withSomeOfArgs([$importFile])
        ->andThrow(new ImportFailedException())
        ->getMock();

    prepareImportZip($filename);

    $importPage = new Import();
    $importPage->files = [
        $importFile,
    ];
    $importPage->submit($buildContextService, $zipImporter);

    Notification::assertNotified(__('import.failed'));
});
