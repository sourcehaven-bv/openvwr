<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Collections\SnapshotCollection;
use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Pages\Contracts\SavesConcepts;
use App\Filament\Resources\SnapshotResource;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Concept;
use App\Models\States\Snapshot\InReview;
use App\Models\States\SnapshotState;
use App\Services\Snapshot\SnapshotStateTransitionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Cancel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function implode;
use function sprintf;

/**
 * Starts the approval process for the record being edited: save, then send its concept
 * to review.
 *
 * It lives on the edit page rather than on the version, because that is where the form
 * is. Required fields are enforced here, and a missing one becomes an ordinary
 * validation error on the field itself — so the wizard marks the step and the user is
 * taken to what needs filling in, instead of reading a list of field names in a corner
 * notification and having to find them again.
 *
 * Saving first is deliberate: a user who filled in the last field and pressed this
 * button means "submit this", not "submit whatever was stored before". Making them
 * press save first would be the computer asking the human to state the obvious.
 */
class SubmitForReviewAction extends Action
{
    public static function make(?string $name = 'snapshot_submit_for_review'): static
    {
        return parent::make($name)
            ->label(__('snapshot.submit_for_review'))
            ->icon('heroicon-o-paper-airplane')
            // Visible whenever the user may submit, full stop — no state check.
            //
            // The button sits on the form, and its whole point is "save what I typed and
            // send it in". Any condition based on the stored versions is a condition on
            // what was saved *before* the current edit, so it hides the button exactly
            // when someone is typing — which is when they need it. That is worse than an
            // occasional press with nothing to submit, which is harmless: submitting
            // saves first, and the concept it then sends in is whatever is in the form.
            ->visible(static function (Model $record): bool {
                return $record instanceof SnapshotSource
                    && Authorization::hasPermission(Permission::SNAPSHOT_CREATE);
            })
            // Saving and validating happens while mounting, before any modal can open.
            //
            // Filament keeps a modal open when the action throws a validation error — it
            // assumes the error belongs to a form inside the modal — which would cover the
            // very fields being reported. Doing the strict save here means a missing field
            // lands on the field with nothing mounted on top of it, and it also settles
            // what the concept contains before we ask whether to supersede a pending
            // version: the question is about the versions, not about the form.
            ->mountUsing(static function (Component $livewire): void {
                self::saveConcept($livewire);
            })
            // Only asked when a version is already in review or approved. Submitting
            // supersedes it — the older one is marked "vervallen" — which throws away work
            // the reviewers or approvers have already done, so it is the user's call.
            //
            // Hidden rather than not configured, because whether to ask depends on what
            // the save above just wrote: a record with nothing pending gets no modal and
            // the action runs straight through, which is the ordinary path.
            ->requiresConfirmation()
            ->modalHidden(static function (Component $livewire): bool {
                return self::getPendingSnapshots(self::resolveRecord($livewire))->isEmpty();
            })
            ->modalHeading(__('snapshot.submit_for_review_pending_heading'))
            ->modalDescription(static function (Component $livewire): string {
                return self::describePendingSnapshots(
                    self::getPendingSnapshots(self::resolveRecord($livewire)),
                );
            })
            ->modalSubmitActionLabel(__('snapshot.submit_for_review_pending_confirm'))
            ->modalCancelActionLabel(__('general.no'))
            ->action(static function (Component $livewire): void {
                self::submitConcept($livewire);
            });
    }

    /**
     * Saves the live form the strict way, so the concept holds what is being submitted.
     *
     * The concept pages relax `required` while saving (see DraftableForm); this asks for
     * the unrelaxed rules, which is exactly the difference between "opslaan" and
     * "indienen" — and it makes a missing field land on the field itself.
     */
    private static function saveConcept(Component $livewire): void
    {
        Assert::isInstanceOf($livewire, EditRecord::class);
        Assert::isInstanceOf($livewire, SavesConcepts::class);

        try {
            // Saving with required fields enforced: a missing one aborts here as an
            // ordinary validation error on the field, so nothing is stored half-submitted.
            $livewire->withRequiredFieldsEnforced(static function () use ($livewire): void {
                $livewire->save(shouldRedirect: false);
            });
        } catch (ValidationException $validationException) {
            // Cancel rather than letting the exception through: Filament unmounts the
            // action for a Cancel, so the errors land on the fields with nothing mounted
            // on top of them. Letting it propagate would leave the action mounted, and the
            // confirmation below would then be shown over the very fields being reported.
            $livewire->setErrorBag($validationException->validator->errors());

            throw new Cancel();
        }
    }

    /**
     * Moves the concept saved during mounting to review.
     *
     * Any version already in review or approved is superseded by this one — the
     * transition service marks those "vervallen" — which is what the confirmation in
     * front of this asked about.
     */
    private static function submitConcept(Component $livewire): void
    {
        $record = self::resolveRecord($livewire);

        /** @var SnapshotStateTransitionService $snapshotStateTransitionService */
        $snapshotStateTransitionService = app(SnapshotStateTransitionService::class);
        $snapshotStateTransitionService->transitionSnapshotsToObsolete(self::getPendingSnapshots($record));

        $snapshot = self::resolveConcept($record);
        $reviewState = SnapshotState::make(SnapshotState::REVIEW_STATE::$name, $snapshot);
        Assert::isInstanceOf($reviewState, SnapshotState::class);

        $snapshotStateTransitionService->transitionToSnapshotState($snapshot, $reviewState);

        Notification::make()
            ->title(__('snapshot.submitted_for_review'))
            ->success()
            ->send();

        // Straight to the version that was just created: it is now fixed and under
        // review, so it — not the form — is what the user wants to look at next.
        $livewire->redirect(
            SnapshotResource::getUrl('view', ['record' => $snapshot]),
            navigate: false,
        );
    }

    /**
     * The concept that the save above just wrote.
     *
     * Saving a concept page always stores one (see StoresConceptSnapshot), so its absence
     * would mean that contract is broken rather than that this record has nothing to
     * submit — hence an assertion instead of a fallback that would quietly paper over it.
     *
     * @param Model&SnapshotSource $record
     */
    private static function resolveConcept(SnapshotSource $record): Snapshot
    {
        $concept = $record->getSnapshotsWithState(Concept::class)->first();
        Assert::isInstanceOf($concept, Snapshot::class);

        return $concept;
    }

    /**
     * @return Model&SnapshotSource
     */
    private static function resolveRecord(Component $livewire): SnapshotSource
    {
        Assert::isInstanceOf($livewire, EditRecord::class);

        $record = $livewire->getRecord();
        Assert::isInstanceOf($record, SnapshotSource::class);

        return $record;
    }

    /**
     * The versions this submission would supersede: the ones already in the approval
     * process.
     *
     * Concept and established are not among them. A concept is the form itself and is
     * rewritten by saving; an established version is the one in force and stays in force
     * until the new one is established in its turn. In review and approved are the states
     * where people are part-way through work that submitting again throws away, which is
     * exactly what the user is asked about.
     */
    private static function getPendingSnapshots(SnapshotSource $record): SnapshotCollection
    {
        $inReviewSnapshots = $record->getSnapshotsWithState(InReview::class);
        $approvedSnapshots = $record->getSnapshotsWithState(Approved::class);

        return $inReviewSnapshots->concat($approvedSnapshots);
    }

    /**
     * Names the versions that would be marked "vervallen", with the status each is in, so
     * the user is deciding about identifiable versions rather than about a count.
     */
    private static function describePendingSnapshots(SnapshotCollection $pendingSnapshots): string
    {
        $descriptions = $pendingSnapshots
            ->map(static function (Snapshot $snapshot): string {
                return __('snapshot.submit_for_review_pending_description', [
                    'version' => $snapshot->version,
                    'state' => __(sprintf('snapshot_state.label.%s', $snapshot->state::$name)),
                ]);
            })
            ->all();

        return implode(' ', $descriptions);
    }
}
