<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

use function __;

class AvgGoalsRepeatableEntry extends RepeatableEntry
{
    public static function make(?string $name = 'avgGoals'): static
    {
        return parent::make($name)
            ->label(__('avg_goal.model_plural'))
            ->placeholder(__('general.none_selected'))
            ->schema([
                TextEntry::make('goal')
                    ->label(__('avg_goal.goal')),
                TextEntry::make('avg_goal_legal_base')
                    ->label(__('avg_goal_legal_base.model_plural')),
                TextEntry::make('remarks')
                    ->label(__('avg_goal.remarks')),
            ]);
    }
}
