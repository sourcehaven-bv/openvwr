<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\OrganisationSnapshotApprovalResource\OrganisationSnapshotApprovalResourceTable;
use App\Filament\Resources\OrganisationSnapshotApprovalResource\Pages\ListOrganisationSnapshotApprovalItems;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Snapshot;
use BackedEnum;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

use function __;

class OrganisationSnapshotApprovalResource extends Resource
{
    protected static ?string $model = Snapshot::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static bool $hasNavigationBadge = true;
    protected static ?int $navigationSort = 2;

    /**
     * v5 routes every access check through getAuthorizationResponse():
     * canCreate(), canEdit() and friends call it directly, and can() is
     * only a thin wrapper around it. Overriding can() alone would leave
     * those paths on the policy and lock the page behind a 403.
     */
    public static function getAuthorizationResponse(string|UnitEnum $action, ?Model $record = null): Response
    {
        return Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_ORGANISATION_OVERVIEW)
            ? Response::allow()
            : Response::deny();
    }

    public static function table(Table $table): Table
    {
        return OrganisationSnapshotApprovalResourceTable::table($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::OVERVIEWS->value);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrganisationSnapshotApprovalItems::route('/'),
            'view' => ViewSnapshot::route('{record}'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('snapshot_approval.organisation');
    }
}
