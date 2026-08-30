<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Repeater;

use Filament\Actions\Action;
use App\Facades\Authentication;
use App\Filament\Forms\FormHelper;
use App\Filament\TenantScoped;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Webmozart\Assert\Assert;

use function __;

class AvgGoalsRepeater extends Repeater
{
    public static function make(string $name = 'avgGoals'): static
    {
        return parent::make($name)
            ->label(__('avg_goal.model_plural'))
            ->relationship(modifyQueryUsing: TenantScoped::getAsClosure())
            ->schema(self::getAvgGoalSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn()
            ->itemLabel(static function (array $state): ?string {
                Assert::keyExists($state, 'goal');
                Assert::nullOrString($state['goal']);

                return $state['goal'];
            })
            ->addActionLabel(__('avg_goal.add_action_label'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    private static function getAvgGoalSchema(): array
    {
        $options = __('avg_goal_legal_base.options');
        Assert::allString($options);

        return [
            Textarea::make('goal')
                ->label(__('avg_goal.goal'))
                ->required()
                ->columnSpanFull(),
            Radio::make('avg_goal_legal_base')
                ->label(__('avg_goal_legal_base.model_plural'))
                ->options(FormHelper::setValueAsKey($options))
                ->required()
                ->live(),
            Textarea::make('remarks')
                ->label(__('avg_goal.remarks'))
                ->columnSpanFull()
                ->required(),
            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }
}
