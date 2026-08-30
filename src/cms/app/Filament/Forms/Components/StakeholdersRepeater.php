<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use App\Facades\Authentication;
use App\Filament\Forms\FormHelper;
use App\Filament\TenantScoped;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Webmozart\Assert\Assert;

use function __;

class StakeholdersRepeater extends Repeater
{
    public static function make(string $name = 'stakeholders'): static
    {
        return parent::make($name)
            ->label(__('stakeholder.model_plural'))
            ->relationship(modifyQueryUsing: TenantScoped::getAsClosure())
            ->schema(self::getStakeholderSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn()
            ->itemLabel(static function (array $state): ?string {
                Assert::keyExists($state, 'description');
                Assert::nullOrString($state['description']);

                return $state['description'];
            })
            ->addActionLabel(__('stakeholder.add_action_label'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    private static function getStakeholderSchema(): array
    {
        return [
            Textarea::make('description')
                ->label(__('general.description'))
                ->required()
                ->rows(5)
                ->columnSpanFull()
                ->maxLength(512),
            Section::make(__('stakeholder.special_collected_data'))
                ->description(__('stakeholder.special_collected_data_description'))
                ->schema([
                    Toggle::make('biometric')
                        ->label(__('stakeholder.biometric'))
                        ->live(),
                    Toggle::make('faith_or_belief')
                        ->label(__('stakeholder.faith_or_belief'))
                        ->live(),
                    Toggle::make('genetic')
                        ->label(__('stakeholder.genetic'))
                        ->live(),
                    Toggle::make('health')
                        ->label(__('stakeholder.health'))
                        ->live(),
                    Toggle::make('political_attitude')
                        ->label(__('stakeholder.political_attitude'))
                        ->live(),
                    Toggle::make('race_or_ethnicity')
                        ->label(__('stakeholder.race_or_ethnicity'))
                        ->live(),
                    Toggle::make('sexual_life')
                        ->label(__('stakeholder.sexual_life'))
                        ->live(),
                    Toggle::make('trade_association_membership')
                        ->label(__('stakeholder.trade_association_membership'))
                        ->live(),
                    Textarea::make('special_collected_data_explanation')
                        ->label(__('stakeholder.special_collected_data_explanation'))
                        ->required()
                        ->hintIcon('heroicon-o-information-circle', __('stakeholder.special_collected_data_explanation_icon_text'))
                        ->visible(FormHelper::isAnyFieldEnabled([
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
                    Toggle::make('criminal_law')
                        ->label(__('stakeholder.criminal_law')),
                    Toggle::make('citizen_service_numbers')
                        ->label(__('stakeholder.citizen_service_numbers')),
                ]),

            StakeholderDataItemsRepeater::make(),

            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }
}
