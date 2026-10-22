<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\RepeatableEntry;

use function __;

class WpgGoalsRepeatableEntry extends RepeatableEntry
{
    public static function make(?string $name = 'wpgGoals'): static
    {
        return parent::make($name)
            ->label(__('wpg_goal.model_plural'))
            ->placeholder(__('general.none_selected'))
            ->schema([
                TextareaEntry::make('description')
                    ->label(__('wpg_goal.description')),
                ToggleEntry::make('article_8')
                    ->label(__('wpg_goal.article_8')),
                ToggleEntry::make('article_9')
                    ->label(__('wpg_goal.article_9')),
                ToggleEntry::make('article_10_1a')
                    ->label(__('wpg_goal.article_10_1a')),
                ToggleEntry::make('article_10_1b')
                    ->label(__('wpg_goal.article_10_1b')),
                ToggleEntry::make('article_10_1c')
                    ->label(__('wpg_goal.article_10_1c')),
                ToggleEntry::make('article_12')
                    ->label(__('wpg_goal.article_12')),
                ToggleEntry::make('article_13_1')
                    ->label(__('wpg_goal.article_13_1')),
                ToggleEntry::make('article_13_2')
                    ->label(__('wpg_goal.article_13_2')),
                ToggleEntry::make('article_13_3')
                    ->label(__('wpg_goal.article_13_3')),
                TextareaEntry::make('explanation')
                    ->label(__('wpg_goal.explanation')),
            ]);
    }
}
