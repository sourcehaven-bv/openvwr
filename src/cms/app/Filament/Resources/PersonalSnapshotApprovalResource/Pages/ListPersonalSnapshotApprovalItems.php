<?php

declare(strict_types=1);

namespace App\Filament\Resources\PersonalSnapshotApprovalResource\Pages;

use App\Facades\Authentication;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use App\Filament\Resources\PersonalSnapshotApprovalResource;
use App\Models\Builders\SnapshotApprovalBuilder;
use App\Models\Builders\SnapshotBuilder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListPersonalSnapshotApprovalItems extends ListRecords
{
    use PersistsFiltersInSession;

    public const string TAB_ID_ALL = 'all';
    public const string TAB_ID_REVIEWED = 'reviewed';
    public const string TAB_ID_UNREVIEWED = 'unreviewed';

    protected static string $resource = PersonalSnapshotApprovalResource::class;

    public function getTabs(): array
    {
        return [
            self::TAB_ID_ALL => Tab::make()
                ->label(__('general.all')),
            self::TAB_ID_REVIEWED => Tab::make()
                ->label(__('snapshot_approval.reviewed'))
                ->modifyQueryUsing(static function (SnapshotBuilder $query): void {
                    $query->whereHas('snapshotApprovals', static function (SnapshotApprovalBuilder $query): void {
                        $query->signed()->assignedTo(Authentication::user());
                    });
                }),
            self::TAB_ID_UNREVIEWED => Tab::make()
                ->label(__('snapshot_approval.unreviewed'))
                ->modifyQueryUsing(static function (SnapshotBuilder $query): void {
                    $query->whereHas('snapshotApprovals', static function (SnapshotApprovalBuilder $query): void {
                        $query->unsigned()->assignedTo(Authentication::user());
                    });
                }),
        ];
    }
}
