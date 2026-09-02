<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Pages\Contracts\SavesConcepts;
use App\Filament\Resources\SnapshotResource;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use App\Models\States\SnapshotState;
use App\Services\Snapshot\SnapshotStateTransitionService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use Webmozart\Assert\Assert;

use function __;
use function app;

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
            // Deliberately no confirmation modal. Filament keeps a modal open when the
            // action throws a validation error — it assumes the error belongs to a form
            // inside the modal — which would cover the very fields being reported. Without
            // one, a missing field simply lands on the field and the page stays usable.
            // Nothing is lost by not asking: submitting only saves and moves the concept
            // to review, and the record stays visible and editable either way.
            ->action(static function (Component $livewire): void {
                self::saveAndSubmit($livewire);
            });
    }

    /**
     * Saves the live form the strict way, then moves the concept to review.
     *
     * The concept pages relax `required` while saving (see DraftableForm); this asks for
     * the unrelaxed rules, which is exactly the difference between "opslaan" and
     * "indienen" — and it makes a missing field land on the field itself.
     */
    private static function saveAndSubmit(Component $livewire): void
    {
        Assert::isInstanceOf($livewire, EditRecord::class);
        Assert::isInstanceOf($livewire, SavesConcepts::class);

        // Saving with required fields enforced: a missing one aborts here as an ordinary
        // validation error on the field, so nothing is stored half-submitted.
        $livewire->withRequiredFieldsEnforced(static function () use ($livewire): void {
            $livewire->save(shouldRedirect: false);
        });

        $record = $livewire->getRecord();
        Assert::isInstanceOf($record, SnapshotSource::class);

        $snapshot = self::resolveConcept($record);
        $reviewState = SnapshotState::make(SnapshotState::REVIEW_STATE::$name, $snapshot);
        Assert::isInstanceOf($reviewState, SnapshotState::class);

        /** @var SnapshotStateTransitionService $snapshotStateTransitionService */
        $snapshotStateTransitionService = app(SnapshotStateTransitionService::class);
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
}
