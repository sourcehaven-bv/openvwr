<?php

declare(strict_types=1);

namespace App\Filament\RelationManagers;

use App\Filament\Resources\SnapshotResource;
use App\Filament\Resources\SnapshotResource\Pages\CompareSnapshots;
use App\Filament\Resources\SnapshotResource\SnapshotResourceTable;
use App\Models\Contracts\SnapshotSource;
use Filament\Facades\Filament;
use Filament\Tables\Actions\Action;
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
