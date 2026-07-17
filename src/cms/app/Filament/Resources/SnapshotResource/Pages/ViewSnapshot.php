<?php

declare(strict_types=1);

namespace App\Filament\Resources\SnapshotResource\Pages;

use App\Enums\Authorization\Permission;
use App\Enums\Snapshot\SnapshotApprovalStatus;
use App\Facades\Authentication;
use App\Facades\Authorization;
use App\Filament\Actions\ExportToPdfAction;
use App\Filament\Infolists\Tabs\Snapshot\ViewApprovalTab;
use App\Filament\Infolists\Tabs\Snapshot\ViewHistoryTab;
use App\Filament\Infolists\Tabs\Snapshot\ViewInfoTab;
use App\Filament\Resources\PersonalSnapshotApprovalResource;
use App\Filament\Resources\PersonalSnapshotApprovalResource\Pages\ListPersonalSnapshotApprovalItems;
use App\Filament\Resources\SnapshotResource;
use App\Models\Snapshot;
use App\Models\States\SnapshotState;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Resources\Resource;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Spatie\ModelStates\Exceptions\InvalidConfig;
use Webmozart\Assert\Assert;

use function __;

class ViewSnapshot extends ViewRecord
{
    public const string TAB_ID_APPROVAL = 'approval';
    public const string TAB_ID_HISTORY = 'history';
    public const string TAB_ID_INFO = 'info';
    public const string REFRESH_LIVEWIRE_COMPONENT = 'refresh-view-snapshot-event';

    protected static string $resource = SnapshotResource::class;

    public function getBreadcrumbs(): array
    {
        $snapshot = $this->record;
        Assert::isInstanceOf($snapshot, Snapshot::class);
        $snapshoutSource = $snapshot->snapshotSource;

        if ($snapshoutSource === null) {
            return [];
        }

        /** @var class-string<Resource> $resource */
        $resource = Filament::getModelResource($snapshoutSource);
        $resourceUrl = $resource::getGlobalSearchResultUrl($snapshoutSource);

        if ($resourceUrl !== null) {
            return [
                $resourceUrl => __('snapshot.back_to', ['resource' => $resource::getModelLabel()]),
            ];
        }
        return [];
    }

    /**
     * @throws InvalidConfig
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('approve_view_next')
                ->label(__('snapshot_approval.view_next'))
                ->color('success')
                ->visible(static function (Snapshot $record): bool {
                    if (!Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL)) {
                        return false;
                    }

                    return self::getNext($record) !== null;
                })
                ->url(static function (Snapshot $record): ?string {
                    return self::getNextUrl($record);
                }),
            Action::make('approve_view_all')
                ->label(__('snapshot_approval.view_all'))
                ->color('success')
                ->visible(Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_UPDATE_PERSONAL))
                ->url(PersonalSnapshotApprovalResource::getUrl(parameters: [
                    'activeTab' => ListPersonalSnapshotApprovalItems::TAB_ID_UNREVIEWED,
                ])),
            ExportToPdfAction::make(),
            ...$this->getSnapshotWorkflowActions(),
        ];
    }

    #[On(self::REFRESH_LIVEWIRE_COMPONENT)]
    public function render(): View
    {
        return parent::render();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make()
                    ->tabs([
                        ViewInfoTab::make(__('snapshot.tab_info'))
                            ->id(self::TAB_ID_INFO),
                        ViewApprovalTab::make(__('snapshot.tab_approval'))
                            ->id(self::TAB_ID_APPROVAL),
                        ViewHistoryTab::make(__('snapshot.tab_history'))
                            ->id(self::TAB_ID_HISTORY),
                    ])
                    ->contained(false)
                    ->persistTabInQueryString(),
            ])
            ->columns(1);
    }

    /**
     * @return array<Action>
     *
     * @throws InvalidConfig
     */
    private function getSnapshotWorkflowActions(): array
    {
        /** @var Snapshot $snapshot */
        $snapshot = $this->record;
        /** @var array<int, string> $transitionableStates */
        $transitionableStates = $snapshot->state->transitionableStates();

        $actions = [];
        foreach ($transitionableStates as $transitionableState) {
            /** @var SnapshotState $snapshotState */
            $snapshotState = SnapshotState::make($transitionableState, $snapshot);
            $action = $snapshotState::getAction();

            $actions[] = $action::makeForSnapshotState($snapshot, $snapshotState);
        }

        return $actions;
    }

    public static function getNext(Snapshot $current): ?Snapshot
    {
        return Snapshot::whereNot('id', $current->id->toString())
            ->whereHas('snapshotApprovals', static function (Builder $query): void {
                $query->where('assigned_to', Authentication::user()->id)
                    ->whereNotIn('status', SnapshotApprovalStatus::signed());
            })
            ->first();
    }

    public static function getNextUrl(Snapshot $current): ?string
    {
        $next = self::getNext($current);
        Assert::notNull($next);

        return self::getUrl(['record' => $next]);
    }
}
