<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Enums\Authorization\Role;
use App\Enums\Notification\NotificationStream;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\CreateAvgResponsibleProcessingRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Mail\SnapshotApproval\ApprovalRequest;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Organisation;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\InReview;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Tests\Helpers\Model\OrganisationTestHelper;

use function expect;
use function it;

// Saving used to leave a record without any version until the user pressed "Versie
// aanmaken". There is now always a version: saving writes a concept snapshot, and the
// reviewers are told about it only once that concept is sent to review.

// Saves a record so it gets a concept snapshot, then sends that concept to review —
// the moment the reviewers are notified.
$sendConceptToReview = function (Organisation $organisation): void {
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->call('save');

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();
    $snapshot->state->transitionTo(InReview::class);
};

it('creates a concept snapshot on save', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(0);

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(1)
        ->and($avgResponsibleProcessingRecord->snapshots->first()->state)
        ->toBeInstanceOf(Concept::class);
});

it('creates a concept snapshot on create', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(CreateAvgResponsibleProcessingRecord::class)
        ->fillForm(['name' => 'Nieuwe verwerking'])
        ->call('create')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::query()->sole();

    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(1)
        ->and($avgResponsibleProcessingRecord->snapshots->first()->state)
        ->toBeInstanceOf(Concept::class);
});

it('updates the same concept snapshot when saving again', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->call('save');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Gewijzigde naam'])
        ->call('save');

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(1)
        ->and($avgResponsibleProcessingRecord->snapshots->first()->name)
        ->toBe('Gewijzigde naam');
});

// Saving twice without touching the form leaves the concept alone rather than putting a
// second version next to it — and once that concept is under review, saving again writes
// nothing at all, because there is nothing the version does not already say.
it('writes no new version when saving changes nothing', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->call('save');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(InReview::class);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->call('save')
        ->assertHasNoFormErrors();

    $avgResponsibleProcessingRecord->refresh();
    expect($avgResponsibleProcessingRecord->snapshots)
        ->toHaveCount(1)
        ->and($avgResponsibleProcessingRecord->snapshots->first()->state)
        ->toBeInstanceOf(InReview::class);
});

it('does not notify anyone while the version is still a concept', function (): void {
    Mail::fake();

    $organisation = OrganisationTestHelper::create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create(['notification_exclusions' => []]);
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->call('save');

    Mail::assertNotQueued(ApprovalRequest::class);
});

it('will notify po when the concept goes to review', function () use ($sendConceptToReview): void {
    Mail::fake();

    $organisation = OrganisationTestHelper::create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create(['notification_exclusions' => []]);

    $sendConceptToReview->call($this, $organisation);

    Mail::assertQueued(ApprovalRequest::class);
});

it('will notify cpo when the concept goes to review', function () use ($sendConceptToReview): void {
    Mail::fake();

    $organisation = OrganisationTestHelper::create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::CHIEF_PRIVACY_OFFICER, $organisation)
        ->create(['notification_exclusions' => []]);

    $sendConceptToReview->call($this, $organisation);

    Mail::assertQueued(ApprovalRequest::class);
});

it('will not notify a po that excluded the stream', function () use ($sendConceptToReview): void {
    Mail::fake();

    $organisation = OrganisationTestHelper::create();
    User::factory()
        ->hasAttached($organisation)
        ->hasOrganisationRole(Role::PRIVACY_OFFICER, $organisation)
        ->create([
            'notification_exclusions' => [NotificationStream::SNAPSHOT_CREATED],
        ]);

    $sendConceptToReview->call($this, $organisation);

    Mail::assertNotQueued(ApprovalRequest::class);
});
