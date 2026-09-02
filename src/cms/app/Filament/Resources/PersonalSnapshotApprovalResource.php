<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\Authorization\Permission;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\NavigationGroups\NavigationGroup;
use App\Filament\Resources\PersonalSnapshotApprovalResource\Pages\ListPersonalSnapshotApprovalItems;
use App\Filament\Resources\PersonalSnapshotApprovalResource\PersonalSnapshotApprovalResourceTable;
use App\Filament\Resources\SnapshotResource\Pages\ViewSnapshot;
use App\Models\Snapshot;
use BackedEnum;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function __;

class PersonalSnapshotApprovalResource extends Resource
{
    protected static ?string $model = Snapshot::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static bool $hasNavigationBadge = true;
    protected static ?int $navigationSort = 1;

    public static function can(string $action, ?Model $record = null): bool
    {
        return Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL);
    }

    /**
     * @return Builder<Snapshot>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('snapshotApprovals', static function (Builder $query): void {
                $query->where('assigned_to', Authentication::user()->id);
            });
    }

    public static function table(Table $table): Table
    {
        return PersonalSnapshotApprovalResourceTable::table($table);
    }

    public static function getNavigationGroup(): ?string
    {
        return __(NavigationGroup::OVERVIEWS->value);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPersonalSnapshotApprovalItems::route('/'),
            'view' => ViewSnapshot::route('{record}'),
        ];
    }

    public static function getPluralModelLabel(): string
    {
        return __('snapshot_approval.personal');
    }
}
