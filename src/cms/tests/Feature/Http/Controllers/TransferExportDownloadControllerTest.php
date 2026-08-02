<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers;

use App\Enums\RouteName;
use App\Models\User;
use App\Transfer\Export\BundleExporter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

use function it;
use function now;
use function sprintf;

function signedTransferDownloadUrl(string $filename, string $userId): string
{
    return URL::temporarySignedRoute(
        RouteName::TRANSFER_EXPORT_DOWNLOAD->value,
        now()->addDay(),
        ['filename' => $filename, 'user' => $userId],
    );
}

it('downloads the export when the signed-in user matches', function (): void {
    Storage::fake('transfer');

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $filename = 'openvwr-export-20260721.zip';
    Storage::disk(BundleExporter::DISK)
        ->put(sprintf('%s/%s', BundleExporter::EXPORT_DIRECTORY, $filename), 'zip-bytes');

    $this->be($user)
        ->get(signedTransferDownloadUrl($filename, $user->id->toString()))
        ->assertOk()
        ->assertDownload($filename);
});

it('forbids downloading an export that belongs to another user', function (): void {
    Storage::fake('transfer');

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);
    $otherUser = User::factory()->create();

    $filename = 'openvwr-export-20260721.zip';
    Storage::disk(BundleExporter::DISK)
        ->put(sprintf('%s/%s', BundleExporter::EXPORT_DIRECTORY, $filename), 'zip-bytes');

    // signed for a different user than the one that is logged in
    $this->be($user)
        ->get(signedTransferDownloadUrl($filename, $otherUser->id->toString()))
        ->assertForbidden();
});

it('returns not found when the export file no longer exists', function (): void {
    Storage::fake('transfer');

    $organisation = OrganisationTestHelper::create();
    $user = UserTestHelper::createForOrganisation($organisation);

    $this->be($user)
        ->get(signedTransferDownloadUrl('missing.zip', $user->id->toString()))
        ->assertNotFound();
});

it('redirects to login when no user is signed in', function (): void {
    Storage::fake('transfer');

    $user = UserTestHelper::create();

    $this->get(signedTransferDownloadUrl('openvwr-export.zip', $user->id->toString()))
        ->assertRedirect('login');
});
