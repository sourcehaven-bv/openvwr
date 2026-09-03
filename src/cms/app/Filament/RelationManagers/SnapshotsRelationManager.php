<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\Pages\ConceptEditRecord;
use App\Filament\Resources\Resource;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\CompareSnapshots;
use App\Filament\Resources\SnapshotResource\SnapshotResourceTable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Livewire\Features\SupportEvents\Event;
use Webmozart\Assert\Assert;

use function __;
use function is_a;
use function route;

class SnapshotsRelationManager extends RelationManager
{
    protected static string $languageFile = 'snapshot';
    protected static string $relationship = 'snapshots';
    protected static string $resource = SnapshotResource::class;

    public const REFRESH_TABLE_EVENT = 'refresh-snapshots-table-event';

    public function table(Table $table): Table
    {
        return SnapshotResourceTable::table($table)
            // The concept row is the one place in this table where something can still be
            // done to the version, so it carries the same "Start vaststellen" as the
            // header of the edit page — and has to mean the same thing there.
            //
            // Submitting saves the form first, so it can only run where that form is. On
            // the edit page this hands the row straight to the header action, which is
            // the button itself and not a copy of it. Anywhere else — the view page —
            // there is no form to save, so the row links to the page that has one.
            ->actions([
                Action::make('snapshot_submit_for_review')
                    ->label(__('snapshot.submit_for_review'))
                    ->icon('heroicon-o-paper-airplane')
                    // Same two conditions as the header button: the user may submit, and
                    // there is a concept to submit. The row knows the second one for a
                    // fact, because it is the concept.
                    ->visible(function (Snapshot $snapshot): bool {
                        if (!$snapshot->state instanceof Concept) {
                            return false;
                        }

                        if (!Authorization::hasPermission(Permission::SNAPSHOT_CREATE)) {
                            return false;
                        }

                        return $this->submitsOnThisPage() || $this->getOwnerEditUrl() !== null;
                    })
                    ->action(function (): void {
                        $this->submitForReview();
                    })
                    ->url(function (): ?string {
                        return $this->submitsOnThisPage() ? null : $this->getOwnerEditUrl();
                    }),
            ])
            ->headerActions([
                Action::make('compare')
                    ->label(__('snapshot.compare'))
                    ->icon('heroicon-o-arrows-right-left')
                    ->visible(fn (): bool => $this->getSnapshotSource()->hasComparableSnapshots())
                    ->url(function (): string {
                        $latest = $this->getSnapshotSource()->getLatestSnapshot();
                        Assert::notNull($latest);

                        return route(CompareSnapshots::getRouteName(), [
                            'tenant' => Filament::getTenant(),
                            'record' => $latest,
                        ]);
                    }),
            ]);
    }

    /**
     * Whether the page around this table is the one that can submit: the owner's edit
     * page, with the form that submitting has to save first.
     */
    private function submitsOnThisPage(): bool
    {
        return is_a($this->getPageClass(), ConceptEditRecord::class, true);
    }

    /**
     * Hands the row over to the header button on the page around this table.
     *
     * The table is its own Livewire component, so it cannot run that action itself: the
     * action saves the owner's form, and that form lives on the page, not here. Asking
     * the page to run its own action is therefore not indirection for its own sake — it
     * is the only way the row can mean the same thing the button does, instead of a
     * second implementation that would drift from it.
     */
    private function submitForReview(): void
    {
        $event = $this->dispatch(ConceptEditRecord::SUBMIT_FOR_REVIEW_EVENT);
        Assert::isInstanceOf($event, Event::class);

        $event->to($this->getPageClass());
    }

    /**
     * The edit page of the record this table belongs to, where "Start vaststellen" lives.
     */
    private function getOwnerEditUrl(): ?string
    {
        $owner = $this->getOwnerRecord();

        /** @var class-string<Resource>|null $resource */
        $resource = Filament::getModelResource($owner);

        if ($resource === null || !$resource::canEdit($owner)) {
            return null;
        }

        return $resource::getUrl('edit', ['record' => $owner]);
    }

    private function getSnapshotSource(): SnapshotSource
    {
        $owner = $this->getOwnerRecord();
        Assert::isInstanceOf($owner, SnapshotSource::class);

        return $owner;
    }

    #[On(self::REFRESH_TABLE_EVENT)]
    public function refreshTableEventListener(): void
    {
        $this->resetTable();
    }
}
