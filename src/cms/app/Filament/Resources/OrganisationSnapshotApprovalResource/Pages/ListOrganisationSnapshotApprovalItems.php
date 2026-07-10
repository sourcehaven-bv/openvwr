<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganisationSnapshotApprovalResource\Pages;

use App\Filament\Resources\OrganisationSnapshotApprovalResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use App\Models\Builders\SnapshotBuilder;
use App\Models\States\Snapshot\Approved;
use App\Models\States\Snapshot\Established;
use App\Models\States\Snapshot\InReview;
use App\Models\States\Snapshot\Obsolete;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListOrganisationSnapshotApprovalItems extends ListRecords
{
    use PersistsFiltersInSession;

    public const string TAB_ID_ALL = 'all';
    public const string TAB_ID_ESTABLISHED_OBSOLETE = 'established-obsolete';
    public const string TAB_ID_INREVIEW_APPROVED = 'in_review-approved';

    protected static string $resource = OrganisationSnapshotApprovalResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('general.all')),
            'established-obsolete' => Tab::make()
                ->label(__('snapshot_approval.established-obsolete'))
                ->modifyQueryUsing(static function (SnapshotBuilder $query): void {
                    $query->whereIn('state', [
                        Established::$name,
                        Obsolete::$name,
                    ]);
                }),
            'in_review-approved' => Tab::make()
                ->label(__('snapshot_approval.in_review-approved'))
                ->modifyQueryUsing(static function (SnapshotBuilder $query): void {
                    $query->whereIn('state', [
                        InReview::$name,
                        Approved::$name,
                    ]);
                }),
        ];
    }
}
