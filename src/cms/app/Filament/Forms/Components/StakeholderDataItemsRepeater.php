<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Facades\Authentication;
use App\Filament\Forms\FormHelper;
use App\Filament\TenantScoped;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Webmozart\Assert\Assert;

use function __;

class StakeholderDataItemsRepeater extends Repeater
{
    public static function make(string $name = 'stakeholder_data_items'): static
    {
        return parent::make($name)
            ->label(__('stakeholder_data_item.model_plural'))
            ->relationship('stakeholderDataItems', TenantScoped::getAsClosure())
            ->schema(self::getStakeholderDataItemSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn()
            ->itemLabel(static function (array $state): ?string {
                Assert::keyExists($state, 'description');
                Assert::nullOrString($state['description']);

                return $state['description'];
            })
            ->addActionLabel(__('stakeholder_data_item.add_action_label'))
            ->deleteAction(static function (Action $action) {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<Component>
     */
    private static function getStakeholderDataItemSchema(): array
    {
        return [
            Textarea::make('description')
                ->label(__('general.description'))
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->maxLength(512),
            Textarea::make('collection_purpose')
                ->label(__('stakeholder_data_item.collection_purpose'))
                ->helperText(__('stakeholder_data_item.help_collection_purpose'))
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->maxLength(512),
            Textarea::make('retention_period')
                ->label(__('stakeholder_data_item.retention_period'))
                ->helperText(__('stakeholder_data_item.help_retention_period'))
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->maxLength(512),
            Textarea::make('source_description')
                ->label(__('stakeholder_data_item.source_description'))
                ->helperText(__('stakeholder_data_item.help_source_description'))
                ->required()
                ->rows(3)
                ->columnSpanFull()
                ->maxLength(512),
            Toggle::make('is_stakeholder_mandatory')
                ->label(__('stakeholder_data_item.is_stakeholder_mandatory'))
                ->live(),
            Textarea::make('stakeholder_consequences')
                ->label(__('stakeholder_data_item.stakeholder_consequences'))
                ->helperText(__('stakeholder_data_item.help_stakeholder_consequences'))
                ->required(FormHelper::isFieldEnabled('is_stakeholder_mandatory'))
                ->rows(3)
                ->columnSpanFull()
                ->maxLength(512),
            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }
}
