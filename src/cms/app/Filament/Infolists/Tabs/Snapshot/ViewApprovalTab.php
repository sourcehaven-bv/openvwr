<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Tabs\Snapshot;

use App\Enums\Authorization\Permission;
use App\Facades\Authorization;
use App\Facades\Snapshot as SnapshotFacade;
use App\Models\Snapshot;
use Closure;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;

use function sprintf;

class ViewApprovalTab extends Tab
{
    public static function make(Htmlable|Closure|string|null $label = null): static
    {
        return parent::make($label)
            ->icon('heroicon-o-check-circle')
            ->badge(static function (Snapshot $snapshot): string {
                return sprintf('%s / %s', SnapshotFacade::countApproved($snapshot), SnapshotFacade::countTotal($snapshot));
            })
            ->visible(Authorization::hasPermission(Permission::SNAPSHOT_APPROVAL_VIEW))
            ->schema([
                Group::make([
                    ViewEntry::make('snapshot_approvals')
                        ->view('filament.infolists.components.entries.snapshot_approvals'),
                ])->extraAttributes(['class' => 'mb-3']),
                Group::make([
                    ViewEntry::make('snapshot_approval_logs')
                        ->view('filament.infolists.components.entries.snapshot_approval_logs'),
                ]),
            ]);
    }
}
