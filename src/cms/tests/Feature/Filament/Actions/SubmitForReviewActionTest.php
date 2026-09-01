<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use Tests\Helpers\Model\OrganisationTestHelper;

use function expect;
use function it;

// "Start vaststellen" lives on the record's edit page, because that is where the form is
// and therefore where an incomplete record can actually be fixed.

it('submits the concept for review from the edit page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_submit_for_review')
        ->assertHasNoFormErrors();

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();

    expect($snapshot->state)->toBeInstanceOf(InReview::class);
});

// The version is fixed and under review once submitted, so that is what the user is
// taken to rather than being left on the form that can no longer change it.
it('redirects to the submitted version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->callAction('snapshot_submit_for_review')
        ->assertRedirect(SnapshotResource::getUrl('view', [
            'record' => $avgResponsibleProcessingRecord->refresh()->snapshots->sole(),
        ]));
});

// The button sits on the form, so it must be there while the user is working — not only
// once their changes happen to be saved as a concept.
it('offers the button on an unsaved change, without saving first', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();
    $snapshot->state->transitionTo(Established::class);

    // Typed but not saved: the button has to be available, because pressing it is what
    // saves those changes into the concept it then submits.
    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Nog niet opgeslagen'])
        ->assertActionVisible('snapshot_submit_for_review');
});

// The button stays put even while a version is under review. Hiding it would depend on
// what was saved before the current edit, which is exactly what makes it disappear while
// someone is typing; submitting again simply starts the next round from the form.
it('stays visible while a version is under review', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->assertActionVisible('snapshot_submit_for_review');
});

// Pressing it again while a version is under review is allowed, so it has to behave: the
// newer submission supersedes the pending one rather than colliding with it.
it('supersedes a pending version when submitted again', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Tweede ronde'])
        ->callAction('snapshot_submit_for_review')
        ->assertHasNoFormErrors();

    $snapshots = $avgResponsibleProcessingRecord->refresh()->snapshots->sortBy('version')->values();

    expect($snapshots)->toHaveCount(2)
        ->and($snapshots->first()->state)->toBeInstanceOf(Obsolete::class)
        ->and($snapshots->last()->state)->toBeInstanceOf(InReview::class)
        ->and($snapshots->last()->name)->toBe('Tweede ronde');
});

// The case the button exists for: an unsaved edit made after submitting. There is no
// concept in the database at this point, so any state-based condition would hide it.
it('stays visible on an unsaved edit made after submitting', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Aangepast, nog niet opgeslagen'])
        ->assertActionVisible('snapshot_submit_for_review');
});

// Editing after submitting writes a fresh concept, so the record can be submitted again.
it('offers the button again after the form is changed and saved', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Aangepast na indienen'])
        ->call('save')
        ->assertActionVisible('snapshot_submit_for_review');
});

// Filament keeps a confirmation modal open on a validation error, which would cover the
// very fields being reported. Validating before the modal opens avoids that entirely.
it('does not open the confirmation modal for an incomplete record', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fillForm(['name' => ''])
        // Validating during the mount is what keeps the modal shut: the errors land on the
        // fields below and the action leaves nothing mounted to confirm.
        ->callAction('snapshot_submit_for_review')
        ->assertHasFormErrors(['name']);

    expect($avgResponsibleProcessingRecord->refresh()->snapshots)
        ->toHaveCount(0);
});

// An established version is finished, and the next round starts from the form, so the
// button is there before anything has been changed or saved.
it('stays visible on an established version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();
    $snapshot->state->transitionTo(Established::class);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->assertActionVisible('snapshot_submit_for_review');
});

it('is offered again after a new concept is saved on top of an established version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();
    $snapshot->state->transitionTo(Established::class);

    // Editing again writes a fresh concept, so the record can be submitted once more.
    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Volgende ronde'])
        ->call('save')
        ->assertActionVisible('snapshot_submit_for_review');
});

// A concept mirrors the form, so there is nothing fixed to look at and no status to move
// from the version page; both belong to the record itself.
it('does not offer a status change on a concept version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->call('save');

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();

    $test->createLivewireTestable(ViewSnapshot::class, ['record' => $snapshot->id])
        ->assertInfolistActionHidden(ViewInfoTab::SECTION_KEY_STATUS_FLOW, 'snapshot_status_change');
});
