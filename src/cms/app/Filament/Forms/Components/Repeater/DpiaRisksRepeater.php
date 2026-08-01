<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Repeater;

use App\Enums\Dpia\RiskLevel;
use App\Facades\Authentication;
use App\Filament\TenantScoped;
use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function is_string;

/**
 * Paragraaf 16: the risks for the rights and freedoms of data subjects.
 *
 * Each risk carries its own motivation fields. That is deliberate: the value of
 * a DPIA is in the reasoning, and a bare "hoog" without a why cannot be
 * reviewed by an FG or defended to the AP.
 */
class DpiaRisksRepeater extends Repeater
{
    public static function make(string $name = 'risks'): static
    {
        return parent::make($name)
            ->label(__('dpia_record.risks'))
            ->relationship(modifyQueryUsing: TenantScoped::getAsClosure())
            ->schema(self::getRiskSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn('order_column')
            ->itemLabel(static function (array $state): string {
                $title = $state['title'] ?? null;

                return is_string($title) && $title !== ''
                    ? $title
                    : __('dpia_record.risk_item_label');
            })
            ->addActionLabel(__('dpia_record.add_risk'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<Component>
     */
    private static function getRiskSchema(): array
    {
        return [
            // A short name first: this is what paragraaf 17 shows in its
            // checkboxes and what the repeater header displays, where a whole
            // paragraph would be unreadable.
            TextInput::make('title')
                ->label(__('dpia_record.risk_title'))
                ->helperText(__('dpia_record.help_risk_title'))
                ->required()
                ->maxLength(255)
                // Paragraaf 17 reads these titles, so the state has to keep up
                // while the invuller is still typing.
                ->live(onBlur: true)
                ->columnSpanFull(),
            Textarea::make('description')
                ->label(__('dpia_record.risk_description'))
                ->helperText(__('dpia_record.help_risk_description'))
                ->rows(4)
                ->columnSpanFull(),
            Textarea::make('origin')
                ->label(__('dpia_record.risk_origin'))
                ->helperText(__('dpia_record.help_risk_origin'))
                ->rows(3)
                ->columnSpanFull(),
            Grid::make()
                ->schema([
                    Select::make('likelihood')
                        ->label(__('dpia_record.risk_likelihood'))
                        ->helperText(__('dpia_record.help_risk_likelihood'))
                        ->options(RiskLevel::options())
                        ->live()
                        ->afterStateUpdated(self::fillSuggestedLevel()),
                    Textarea::make('likelihood_motivation')
                        ->label(__('dpia_record.risk_likelihood_motivation'))
                        ->rows(3),
                    Select::make('impact')
                        ->label(__('dpia_record.risk_impact'))
                        ->helperText(__('dpia_record.help_risk_impact'))
                        ->options(RiskLevel::options())
                        ->live()
                        ->afterStateUpdated(self::fillSuggestedLevel()),
                    Textarea::make('impact_motivation')
                        ->label(__('dpia_record.risk_impact_motivation'))
                        ->rows(3),
                    Select::make('level')
                        ->label(__('dpia_record.risk_level'))
                        ->helperText(__('dpia_record.help_risk_level'))
                        ->options(RiskLevel::options())
                        ->live(),
                    Textarea::make('level_motivation')
                        ->label(__('dpia_record.risk_level_motivation'))
                        ->rows(3),
                ]),
            // Shows what kans x impact suggests, and flags a deviation. The
            // matrix is illustrative, so this nudges rather than overrules:
            // the invuller stays responsible for the final score.
            Placeholder::make('matrix_hint')
                ->hiddenLabel()
                ->content(static function (Get $get): ?HtmlString {
                    return self::matrixHint($get);
                })
                ->columnSpanFull(),
            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }

    /**
     * Fills in the risiconiveau that kans x impact points at, once both are
     * chosen.
     *
     * Only when the field is still empty. The matrix is illustrative and the
     * invuller may deviate from it with an argument, so a level that was
     * already chosen -- deliberately or not -- is never overwritten. Clearing
     * the field makes the suggestion available again.
     */
    private static function fillSuggestedLevel(): Closure
    {
        return static function (Get $get, Set $set): void {
            if (self::levelFrom($get('level')) instanceof RiskLevel) {
                return;
            }

            $suggested = RiskLevel::suggest(
                self::levelFrom($get('likelihood')),
                self::levelFrom($get('impact')),
            );

            if (!$suggested instanceof RiskLevel) {
                return;
            }

            $set('level', $suggested->value);
        };
    }

    private static function matrixHint(Get $get): ?HtmlString
    {
        $likelihood = self::levelFrom($get('likelihood'));
        $impact = self::levelFrom($get('impact'));
        $suggested = RiskLevel::suggest($likelihood, $impact);

        if (!$suggested instanceof RiskLevel) {
            return null;
        }

        $chosen = self::levelFrom($get('level'));

        if ($chosen instanceof RiskLevel && $chosen !== $suggested) {
            return new HtmlString(
                '<p class="text-sm text-warning-600 dark:text-warning-400">'
                . e(__('dpia_record.risk_matrix_deviation', ['level' => $suggested->label()]))
                . '</p>',
            );
        }

        return new HtmlString(
            '<p class="text-sm text-gray-500">'
            . e(__('dpia_record.risk_matrix_suggestion', ['level' => $suggested->label()]))
            . '</p>',
        );
    }

    private static function levelFrom(mixed $value): ?RiskLevel
    {
        // Form state carries the backed value, not the enum; this branch is
        // narrowing for static analysis only.
        // @codeCoverageIgnoreStart
        if ($value instanceof RiskLevel) {
            return $value;
        }
        // @codeCoverageIgnoreEnd

        if (!is_string($value) || $value === '') {
            return null;
        }

        return RiskLevel::tryFrom($value);
    }
}
