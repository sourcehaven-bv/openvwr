<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Actions;

use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\Resources\AvgResponsibleProcessingRecordResource\Pages\EditAvgResponsibleProcessingRecord;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use App\Models\States\SnapshotState;
use App\Services\Snapshot\SnapshotStateTransitionService;
use App\ValueObjects\CalendarDate;
use Tests\Helpers\Model\OrganisationTestHelper;

use function __;
use function app;
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

// Superseding a pending version throws away review work that has already been done, so
// the user is asked first rather than finding out afterwards.
it('asks before superseding a version under review', function (): void {
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
        ->mountAction('snapshot_submit_for_review')
        ->assertSee(__('snapshot.submit_for_review_pending_heading'));

    // Only asked, not confirmed: the pending version is untouched and nothing new has been
    // sent in. Saving the concept during the mount is expected, so it is the version under
    // review that must still be there and still be the only submitted one.
    $submittedSnapshots = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->reject(static fn (Snapshot $snapshot): bool => $snapshot->state instanceof Concept);

    expect($submittedSnapshots)->toHaveCount(1)
        ->and($submittedSnapshots->sole()->state)->toBeInstanceOf(InReview::class);
});

// An approved version is even further along than one in review, so it warrants the same
// question before it is thrown away.
it('asks before superseding an approved version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Approved::class);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Tweede ronde'])
        ->mountAction('snapshot_submit_for_review')
        ->assertSee(__('snapshot.submit_for_review_pending_heading'));

    $submittedSnapshots = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->reject(static fn (Snapshot $snapshot): bool => $snapshot->state instanceof Concept);

    expect($submittedSnapshots)->toHaveCount(1)
        ->and($submittedSnapshots->sole()->state)->toBeInstanceOf(Approved::class);
});

// Confirming is what the question is for: the approved version becomes "vervallen" and
// the newly submitted one takes its place in review.
it('marks an approved version as obsolete once confirmed', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Approved::class);

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

// Cancelling has to leave everything as it was, otherwise the question is not a real
// choice: the pending version stays in review and nothing new is submitted.
it('leaves the pending version alone when the question is cancelled', function (): void {
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
        ->mountAction('snapshot_submit_for_review')
        ->unmountAction();

    $submittedSnapshots = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->reject(static fn (Snapshot $snapshot): bool => $snapshot->state instanceof Concept);

    expect($submittedSnapshots)->toHaveCount(1)
        ->and($submittedSnapshots->sole()->state)->toBeInstanceOf(InReview::class);
});

// Nothing is pending on a first submission, so there is nothing to warn about: asking
// anyway would put a modal in front of the ordinary path for no reason.
it('does not ask when no version is pending', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->mountAction('snapshot_submit_for_review')
        ->assertDontSee(__('snapshot.submit_for_review_pending_heading'));

    // Straight through, without a question: the concept was submitted by the same press.
    expect($avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state)
        ->toBeInstanceOf(InReview::class);
});

// An established version is the one in force and stays in force until its successor is
// established, so it is not something this submission supersedes — no question needed.
it('does not ask when the previous version is established', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Established::class);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Volgende ronde'])
        ->mountAction('snapshot_submit_for_review')
        ->assertDontSee(__('snapshot.submit_for_review_pending_heading'));

    $snapshots = $avgResponsibleProcessingRecord->refresh()->snapshots->sortBy('version')->values();

    expect($snapshots)->toHaveCount(2)
        ->and($snapshots->first()->state)->toBeInstanceOf(Established::class)
        ->and($snapshots->last()->state)->toBeInstanceOf(InReview::class);
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
        // fields below and the mount is abandoned before anything can be confirmed.
        ->mountAction('snapshot_submit_for_review')
        ->assertHasFormErrors(['name'])
        ->assertDontSee(__('snapshot.submit_for_review_pending_heading'));

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

// The versions table below the form asks the page to submit, because only the page can
// save that form first. This is the other half of that hand-off: the page listens.
it('submits when the versions table asks the page to', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->fireEvent(ConceptEditRecord::SUBMIT_FOR_REVIEW_EVENT)
        ->assertHasNoFormErrors();

    $snapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();

    expect($snapshot->state)->toBeInstanceOf(InReview::class);
});

// Submitting an unchanged registration would make a version that says exactly what the
// last one says, which is not a version anyone wants to review or establish. It is
// reported instead of being made.
it('reports that nothing changed instead of submitting an identical version', function (): void {
    $organisation = OrganisationTestHelper::create();
    // review_at is optional in the factory and gets defaulted on save, so it is pinned
    // here: an unpinned one would differ between the stored version and the fresh concept
    // and read as a change of its own.
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create(['review_at' => CalendarDate::parse('2027-01-01')]);

    $test = $this->asFilamentOrganisationUser($organisation);

    // Saved through the form first, so the version established below holds exactly what
    // pressing the button again produces — including the fields a save fills in itself.
    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->call('save');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Established::class);

    // Pressed again without touching a thing.
    $component = $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->mountAction('snapshot_submit_for_review');

    expect($component->instance()->getMountedAction()->getModalHeading())
        ->toBe(__('snapshot.submit_for_review_unchanged_heading'));

    // Reported, not submitted: the established version is still the only one there is.
    $submittedSnapshots = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->reject(static fn (Snapshot $snapshot): bool => $snapshot->state instanceof Concept);

    expect($submittedSnapshots)->toHaveCount(1)
        ->and($submittedSnapshots->sole()->state)->toBeInstanceOf(Established::class);
});

// The modal reports a fact rather than asking a question, so there must be nothing to
// press that would submit the identical version anyway.
it('offers no way to submit an identical version from the modal', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create(['review_at' => CalendarDate::parse('2027-01-01')]);

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->call('save');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Established::class);

    $component = $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->mountAction('snapshot_submit_for_review');

    $mountedAction = $component->instance()->getMountedAction();

    // Nothing to press that would submit the identical version; only a way out.
    expect($mountedAction->getModalSubmitAction())->toBeNull();
    expect($mountedAction->getModalCancelActionLabel())->toBe(__('general.close'));
});

// A real edit is what the check must not get in the way of: changed content submits
// normally, without the "geen wijzigingen" modal.
it('submits normally when the registration was changed', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Established::class);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Wel degelijk gewijzigd'])
        ->callAction('snapshot_submit_for_review')
        ->assertHasNoFormErrors();

    $snapshots = $avgResponsibleProcessingRecord->refresh()->snapshots->sortBy('version')->values();

    expect($snapshots)->toHaveCount(2)
        ->and($snapshots->last()->state)->toBeInstanceOf(InReview::class)
        ->and($snapshots->last()->name)->toBe('Wel degelijk gewijzigd');
});

// A version that was withdrawn does not hold the line: going back to what it said is a
// real change against the version that is actually in force, so it must be submittable.
it('still submits when the registration matches only an obsolete version', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $firstSnapshot = $avgResponsibleProcessingRecord->refresh()->snapshots->sole();
    $originalName = $firstSnapshot->name;
    $firstSnapshot->state->transitionTo(Established::class);

    // Change it, establish that, then change it back to what the first version said.
    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Tussentijds gewijzigd'])
        ->callAction('snapshot_submit_for_review');

    $secondSnapshot = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->sortByDesc('version')
        ->first();

    // Through the service, so the first established version is superseded rather than
    // colliding with the second on the unique (source, state) index.
    app(SnapshotStateTransitionService::class)->transitionToSnapshotState(
        $secondSnapshot,
        SnapshotState::make(Established::$name, $secondSnapshot),
    );

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => $originalName])
        ->callAction('snapshot_submit_for_review')
        ->assertHasNoFormErrors();

    $submittedSnapshots = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->reject(static fn (Snapshot $snapshot): bool => $snapshot->state instanceof Concept)
        ->sortBy('version')
        ->values();

    expect($submittedSnapshots)->toHaveCount(3)
        ->and($submittedSnapshots->last()->state)->toBeInstanceOf(InReview::class)
        ->and($submittedSnapshots->last()->name)->toBe($originalName);
});

// Nothing to compare against on a first submission, so the check must not mistake "no
// earlier version" for "no changes".
it('submits the first version even though there is nothing to compare it to', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create();

    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
            'record' => $avgResponsibleProcessingRecord->id,
        ])
        ->mountAction('snapshot_submit_for_review');

    expect($avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state)
        ->toBeInstanceOf(InReview::class);
});

// Confirming is still refused when nothing changed. The modal for that case carries no
// submit button, but the action must not depend on the modal to decide what happens: a
// confirmation that arrives anyway may not produce a version that says nothing new.
it('does not submit an identical version even when the action is called directly', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create(['review_at' => CalendarDate::parse('2027-01-01')]);

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->call('save');

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    $avgResponsibleProcessingRecord->refresh()->snapshots->sole()->state->transitionTo(Established::class);

    // Mounted and then confirmed, without touching the form in between.
    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->mountAction('snapshot_submit_for_review')
        ->callMountedAction();

    $submittedSnapshots = $avgResponsibleProcessingRecord->refresh()->snapshots
        ->reject(static fn (Snapshot $snapshot): bool => $snapshot->state instanceof Concept);

    expect($submittedSnapshots)->toHaveCount(1)
        ->and($submittedSnapshots->sole()->state)->toBeInstanceOf(Established::class);
});

// Submitting moves the concept to review, and the modal's heading and buttons are
// resolved again while that same request renders. By then there is no concept left, which
// has to read as "nothing to compare" rather than fail on a missing one.
it('survives the modal being resolved again after the concept has been submitted', function (): void {
    $organisation = OrganisationTestHelper::create();
    $avgResponsibleProcessingRecord = AvgResponsibleProcessingRecord::factory()
        ->recycle($organisation)
        ->withValidState()
        ->create(['review_at' => CalendarDate::parse('2027-01-01')]);

    $test = $this->asFilamentOrganisationUser($organisation);

    $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])->callAction('snapshot_submit_for_review');

    // Changed, so this is a real submission, and a version is already under review — the
    // modal is shown, confirmed, and then re-resolved with the concept already consumed.
    $component = $test->createLivewireTestable(EditAvgResponsibleProcessingRecord::class, [
        'record' => $avgResponsibleProcessingRecord->id,
    ])
        ->fillForm(['name' => 'Tweede ronde'])
        ->mountAction('snapshot_submit_for_review')
        ->callMountedAction();

    $mountedAction = $component->instance()->getMountedAction();

    if ($mountedAction !== null) {
        expect($mountedAction->getModalHeading())->toBeString();
    }

    $snapshots = $avgResponsibleProcessingRecord->refresh()->snapshots->sortBy('version')->values();

    expect($snapshots)->toHaveCount(2)
        ->and($snapshots->first()->state)->toBeInstanceOf(Obsolete::class)
        ->and($snapshots->last()->state)->toBeInstanceOf(InReview::class);
});
