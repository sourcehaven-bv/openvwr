<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\RepeatableEntry;

use function __;

class StakeholderDataItemsRepeatableEntry extends RepeatableEntry
{
    public static function make(?string $name = 'stakeholderDataItems'): static
    {
        return parent::make($name)
            ->label('')
            ->placeholder(__('general.none_selected'))
            ->schema([
                TextareaEntry::make('description')
                    ->label(__('general.description')),
                TextareaEntry::make('collection_purpose')
                    ->label(__('stakeholder_data_item.collection_purpose')),
                TextareaEntry::make('retention_period')
                    ->label(__('stakeholder_data_item.retention_period')),
                TextareaEntry::make('source_description')
                    ->label(__('stakeholder_data_item.source_description')),
                ToggleEntry::make('is_stakeholder_mandatory')
                    ->label(__('stakeholder_data_item.is_stakeholder_mandatory')),
                TextareaEntry::make('stakeholder_consequences')
                    ->label(__('stakeholder_data_item.stakeholder_consequences')),
            ]);
    }
}
