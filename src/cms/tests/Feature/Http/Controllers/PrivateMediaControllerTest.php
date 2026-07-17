<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\Authorization\Role;
use App\Enums\RouteName;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\PublicWebsiteTree;
use App\Models\User;
use App\Vendor\MediaLibrary\Media;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\SessionTestHelper;

use function it;
use function route;
use function sprintf;

it('redirects when no user is logged in', function (): void {
    $this->get('/media/')->assertRedirect('login');
});

it('redirects on a specific object when no user is logged in', function (): void {
    $media = Media::factory()->create();

    $this->get(sprintf('/media/%s', $media->uuid))->assertRedirect('login');
});

it('redirects on a non-existing object when no user is logged in', function (): void {
    $this->get('/media/non-uuid')->assertRedirect('login');
});

it('aborts when the user is not allowed to view the resource', function (): void {
    $avgProcessorProcessingRecord = AvgProcessorProcessingRecord::factory()->create();

    $media = Media::factory()
        ->create([
            'model_type' => AvgProcessorProcessingRecord::class,
            'model_id' => $avgProcessorProcessingRecord->id,
        ]);
    $user = User::factory()->create();

    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertForbidden();
});

it('returns the media in BinaryResponse when the user is allowed to view the organisation', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $media = Media::factory()->create([
        'model_id' => $organisation->id,
        'model_type' => $organisation::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
    ]);

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    $this->asFilamentOrganisationUser($organisation)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertHeader('Content-Security-Policy');
});

it('returns the media in BinaryResponse when the user is allowed to view the organisation-resource', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasGlobalRole(Role::FUNCTIONAL_MANAGER)
        ->hasOrganisationRole(Role::INPUT_PROCESSOR, $organisation)
        ->create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->for($organisation)
        ->create();
    $media = Media::factory()->create([
        'model_id' => $avgResponsibleProcessingRecord->id,
        'model_type' => $avgResponsibleProcessingRecord::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
    ]);

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertHeader('Content-Security-Policy');
});

it('checks the correct media model for permission', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasGlobalRole(Role::FUNCTIONAL_MANAGER)
        ->hasOrganisationRole(Role::INPUT_PROCESSOR, $organisation)
        ->create();
    Document::factory()->create(); // add document for "another" organisation
    $document = Document::factory()
        ->for($organisation)
        ->create();
    $media = Media::factory()->create([
        'model_id' => $document->id,
        'model_type' => $document::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
    ]);

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    SessionTestHelper::setOtpValid();
    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertHeader('Content-Security-Policy');
});

it('fails if no permission on parent model ', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasGlobalRole(Role::FUNCTIONAL_MANAGER)
        ->hasOrganisationRole(Role::INPUT_PROCESSOR, $organisation)
        ->create();
    $document = Document::factory()->create(); // add document for "another" organisation
    $media = Media::factory()->create([
        'model_id' => $document->id,
        'model_type' => $document::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
    ]);

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    SessionTestHelper::setOtpValid();
    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertForbidden();
});

it('returns not found when media is not yet validated', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $media = Media::factory()->create([
        'model_id' => $organisation->id,
        'model_type' => $organisation::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
        'validated_at' => null,
    ]);

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    $this->asFilamentOrganisationUser($organisation)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertNotFound();
});

it('fails if organisation is deleted', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasGlobalRole(Role::FUNCTIONAL_MANAGER)
        ->hasOrganisationRole(Role::INPUT_PROCESSOR, $organisation)
        ->create();
    $media = Media::factory()->create([
        'model_id' => $organisation->id,
        'model_type' => $organisation::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
    ]);
    $organisation->delete();

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    SessionTestHelper::setOtpValid();
    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertNotFound();
});

it('returns the media with the original content type for non-text mime types', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $media = Media::factory()->create([
        'model_id' => $organisation->id,
        'model_type' => $organisation::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'application/pdf',
    ]);

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    $this->asFilamentOrganisationUser($organisation)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

it('returns not found when the media model has been deleted', function (): void {
    $publicWebsiteTree = PublicWebsiteTree::factory()->create();
    $media = Media::factory()->create([
        'model_id' => $publicWebsiteTree->id,
        'model_type' => $publicWebsiteTree::class,
    ]);
    $publicWebsiteTree->delete();

    $user = User::factory()->create();
    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertNotFound();
});

it('fails if organisation is deleted on parent model', function (): void {
    Storage::fake(ConfigTestHelper::get('media-library.filesystem_disk'));

    $organisation = OrganisationTestHelper::create();
    $user = User::factory()
        ->hasAttached($organisation)
        ->hasGlobalRole(Role::FUNCTIONAL_MANAGER)
        ->hasOrganisationRole(Role::INPUT_PROCESSOR, $organisation)
        ->create();
    $document = Document::factory()
        ->for($organisation)
        ->create();
    $media = Media::factory()->create([
        'model_id' => $document->id,
        'model_type' => $document::class,
        'organisation_id' => $organisation->id,
        'mime_type' => 'text/plain',
    ]);
    $organisation->delete();

    $filesystem = Storage::disk(ConfigTestHelper::get('media-library.filesystem_disk'));
    $filesystem->put(sprintf('%s/%s/%s/%s', $organisation->id, $media->collection_name, $media->uuid, $media->file_name), 'test');

    SessionTestHelper::setOtpValid();
    $this->be($user)
        ->get(route(RouteName::MEDIA_PRIVATE, $media->uuid))
        ->assertNotFound();
});
