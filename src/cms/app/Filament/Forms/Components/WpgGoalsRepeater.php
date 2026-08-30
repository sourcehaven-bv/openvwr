<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use App\Facades\Authentication;
use App\Filament\TenantScoped;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Webmozart\Assert\Assert;

use function __;
use function array_map;
use function is_string;
use function sprintf;

class WpgGoalsRepeater extends Repeater
{
    public static function make(?string $name = 'wpgGoals'): static
    {
        return parent::make($name)
            ->label(__('wpg_goal.model_plural'))
            ->relationship(modifyQueryUsing: TenantScoped::getAsClosure())
            ->schema(self::getWpgGoalSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn()
            ->itemLabel(static function (array $state): ?string {
                Assert::keyExists($state, 'description');
                Assert::nullOrString($state['description']);

                return $state['description'];
            })
            ->addActionLabel(__('wpg_goal.add_action_label'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    private static function getWpgGoalSchema(): array
    {
        return [
            Textarea::make('description')
                ->label(__('wpg_goal.description'))
                ->helperText(__('wpg_goal.help_description'))
                ->required()
                ->columnSpanFull(),
            ...self::getArticleToggles(),
            Textarea::make('explanation')
                ->label(__('wpg_goal.explanation'))
                ->helperText(__('wpg_goal.help_explanation'))
                ->columnSpanFull()
                ->required(),
            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }

    /**
     * @return array<Toggle>
     */
    private static function getArticleToggles(): array
    {
        $fields = [
            'article_8',
            'article_9',
            'article_10_1a',
            'article_10_1b',
            'article_10_1c',
            'article_12',
            'article_13_1',
            'article_13_2',
            'article_13_3',
        ];

        return array_map(static function (string $name) use ($fields) {
            return Toggle::make($name)
                ->label(__(sprintf('wpg_goal.%s', $name)))
                ->helperText(self::getArticleHelperText($name))
                ->accepted(static function (Get $get) use ($fields): bool {
                    foreach ($fields as $field) {
                        if ($get($field) === true) {
                            return false;
                        }
                    }

                    return true;
                })
                ->validationMessages([
                    'accepted' => __('wpg_goal.article_required_message'),
                ])
                ->live();
        }, $fields);
    }

    /**
     * Only the articles whose label does not already explain their scope get helper text;
     * the others would merely repeat their own label.
     */
    private static function getArticleHelperText(string $name): ?string
    {
        $key = sprintf('wpg_goal.help_%s', $name);
        $helperText = __($key);

        if (!is_string($helperText) || $helperText === $key) {
            return null;
        }

        return $helperText;
    }
}
