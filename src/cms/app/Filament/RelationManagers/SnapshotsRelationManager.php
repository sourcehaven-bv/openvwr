<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use Filament\Actions\Action;
use App\Filament\Resources\Resource;
use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\CompareSnapshots;
use App\Filament\Resources\SnapshotResource\SnapshotResourceTable;
use App\Models\Contracts\SnapshotSource;
use App\Models\Snapshot;
use App\Models\States\Snapshot\Concept;
use Filament\Facades\Filament;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Webmozart\Assert\Assert;

use function __;
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
            // done to the version. It links to the record's own form, because that is where
            // an incomplete record gets fixed and where the submit button does its work.
            ->actions([
                Action::make('snapshot_submit_for_review')
                    ->label(__('snapshot.submit_for_review'))
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(static function (Snapshot $snapshot): bool {
                        return $snapshot->state instanceof Concept;
                    })
                    ->url(function (): ?string {
                        return $this->getOwnerEditUrl();
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
