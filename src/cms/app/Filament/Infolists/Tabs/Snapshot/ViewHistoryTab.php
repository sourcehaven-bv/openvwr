<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Tabs\Snapshot;

use Closure;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\Support\Htmlable;

class ViewHistoryTab extends Tab
{
    public static function make(Htmlable|Closure|string|null $label = null): static
    {
        return parent::make($label)
            ->icon('heroicon-o-calendar-days')
            ->schema([
                ViewEntry::make('snapshot_transitions')
                    ->view('filament.infolists.components.entries.snapshot_transitions'),
            ]);
    }
}
