<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Schemas\Components\Section;
use App\Filament\Infolists\InfolistHelper;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;

use function __;

class StakeholdersRepeatableEntry extends RepeatableEntry
{
    public static function make(?string $name = 'stakeholders'): static
    {
        return parent::make($name)
            ->label(__('stakeholder.model_plural'))
            ->placeholder(__('general.none_selected'))
            ->schema([
                Section::make(__('stakeholder.special_collected_data'))
                    ->description(__('stakeholder.special_collected_data_description'))
                    ->schema([
                        TextEntry::make('description')
                            ->label(__('general.description')),
                        ToggleEntry::make('biometric')
                            ->label(__('stakeholder.biometric')),
                        ToggleEntry::make('faith_or_belief')
                            ->label(__('stakeholder.faith_or_belief')),
                        ToggleEntry::make('genetic')
                            ->label(__('stakeholder.genetic')),
                        ToggleEntry::make('health')
                            ->label(__('stakeholder.health')),
                        ToggleEntry::make('political_attitude')
                            ->label(__('stakeholder.political_attitude')),
                        ToggleEntry::make('race_or_ethnicity')
                            ->label(__('stakeholder.race_or_ethnicity')),
                        ToggleEntry::make('sexual_life')
                            ->label(__('stakeholder.sexual_life')),
                        ToggleEntry::make('trade_association_membership')
                            ->label(__('stakeholder.trade_association_membership')),
                        TextareaEntry::make('special_collected_data_explanation')
                            ->label(__('stakeholder.special_collected_data_explanation'))
                            ->visible(InfolistHelper::isAnyFieldEnabled([
                                'biometric',
                                'faith_or_belief',
                                'genetic',
                                'health',
                                'political_attitude',
                                'race_or_ethnicity',
                                'sexual_life',
                                'trade_association_membership',
                            ])),
                    ]),

                Section::make(__('stakeholder.sensitive_data'))
                    ->description(__('stakeholder.sensitive_data_description'))
                    ->schema([
                        ToggleEntry::make('criminal_law')
                            ->label(__('stakeholder.criminal_law')),
                        ToggleEntry::make('citizen_service_numbers')
                            ->label(__('stakeholder.citizen_service_numbers')),
                    ]),

                Section::make()
                    ->heading(__('stakeholder_data_item.model_plural'))
                    ->schema([
                        StakeholderDataItemsRepeatableEntry::make(),
                    ]),
            ]);
    }
}
