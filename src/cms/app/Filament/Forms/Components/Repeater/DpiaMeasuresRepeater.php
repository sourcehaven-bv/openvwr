<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Repeater;

use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use App\Enums\Dpia\MeasureType;
use App\Enums\Dpia\RiskLevel;
use App\Facades\Authentication;
use App\Filament\TenantScoped;
use App\Models\Dpia\DpiaMeasure;
use App\Services\Dpia\DpiaMeasureRiskLinker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

use function __;
use function is_array;
use function is_string;

/**
 * Paragraaf 17: the measures, each linked to the risks it addresses.
 *
 * The risk checkboxes are populated from the risks entered in paragraaf 16 on
 * the same form, so "welke maatregel pakt welk risico aan" is answered by
 * selection instead of by retyping the risk. When a measure leaves a high
 * residual risk, the AP fields appear: that is the artikel 36 escalation.
 */
class DpiaMeasuresRepeater extends Repeater
{
    public static function make(string $name = 'measures'): static
    {
        return parent::make($name)
            ->label(__('dpia_record.measures'))
            ->relationship(modifyQueryUsing: TenantScoped::getAsClosure())
            ->schema(self::getMeasureSchema())
            ->defaultItems(0)
            ->collapsible()
            ->orderColumn('order_column')
            ->itemLabel(static function (array $state): string {
                $description = $state['description'] ?? null;

                return is_string($description) && $description !== ''
                    ? $description
                    : __('dpia_record.measure_item_label');
            })
            ->addActionLabel(__('dpia_record.add_measure'))
            ->deleteAction(static function (Action $action): Action {
                return $action->requiresConfirmation();
            });
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    private static function getMeasureSchema(): array
    {
        return [
            Textarea::make('description')
                ->label(__('dpia_record.measure_description'))
                ->required()
                ->columnSpanFull(),
            // The risks of this DPIA, so "welke maatregel pakt welk risico aan"
            // is answered by selection rather than by retyping the risk.
            //
            // Options come from the sibling risks repeater rather than from a
            // relationship: a risk added in the same session is not in the
            // database yet, and leaving it out would force the invuller to save
            // halfway through paragraaf 16 before they could refer to it.
            //
            // The keys are therefore repeater state keys, not risk ids, so the
            // pivot cannot be written here. DpiaMeasureRiskLinker resolves them
            // after saving, once every risk has an id.
            CheckboxList::make('risks')
                ->label(__('dpia_record.measure_risks'))
                ->helperText(__('dpia_record.help_measure_risks'))
                ->options(static function (Get $get): array {
                    return self::riskOptions($get);
                })
                // The counterpart of DpiaMeasureRiskLinker: the pivot is not
                // part of the measure's attributes, so nothing restores these
                // boxes when an existing DPIA is opened. Without this the form
                // shows every measure as covering no risk at all, and saving
                // that state syncs the pivot away.
                ->afterStateHydrated(static function (CheckboxList $component, ?DpiaMeasure $record): void {
                    // Null on a repeater item that has not been saved yet;
                    // there is no pivot to restore for it.
                    if (!$record instanceof DpiaMeasure) {
                        return;
                    }

                    $component->state(self::linkedRiskKeys($record));
                })
                // Not dehydrated: "risks" is a relation, not a column on
                // dpia_measures, so letting it through would make Eloquent try
                // to fill a non-existent attribute.
                ->dehydrated(false)
                ->columnSpanFull(),
            Grid::make()
                ->schema([
                    Select::make('type')
                        ->label(__('dpia_record.measure_type'))
                        ->helperText(__('dpia_record.help_measure_type'))
                        ->options(MeasureType::options()),
                    TextInput::make('owner')
                        ->label(__('dpia_record.measure_owner'))
                        ->helperText(__('dpia_record.help_measure_owner')),
                    Select::make('residual_level')
                        ->label(__('dpia_record.measure_residual_level'))
                        ->helperText(__('dpia_record.help_measure_residual_level'))
                        ->options(RiskLevel::options())
                        ->live(),
                    Textarea::make('origin')
                        ->label(__('dpia_record.measure_origin'))
                        ->helperText(__('dpia_record.help_measure_origin'))
                        ->rows(3),
                ]),
            // Artikel 36 AVG: a high residual risk means the AP has to be
            // consulted before the processing starts.
            Textarea::make('ap_advice')
                ->label(__('dpia_record.measure_ap_advice'))
                ->helperText(__('dpia_record.help_measure_ap_advice'))
                ->visible(static fn (Get $get): bool => self::hasHighResidualRisk($get))
                ->columnSpanFull(),
            TextInput::make('monitoring_country')
                ->label(__('dpia_record.measure_monitoring_country'))
                ->helperText(__('dpia_record.help_measure_monitoring_country'))
                ->visible(static fn (Get $get): bool => self::hasHighResidualRisk($get)),
            Hidden::make('organisation_id')
                ->default(Authentication::organisation()->id->toString()),
        ];
    }

    /**
     * The saved risks of a measure, as the repeater state keys the checkbox
     * options use.
     *
     * A risk that is already in the database appears in the risks repeater
     * under "record-<uuid>", so the pivot has to be expressed in those same
     * keys to line up with the options; see {@see DpiaMeasureRiskLinker}, which
     * reads them back in that form.
     *
     * @return array<int, string>
     */
    private static function linkedRiskKeys(DpiaMeasure $measure): array
    {
        $keys = [];

        foreach ($measure->risks as $risk) {
            $keys[] = DpiaMeasureRiskLinker::stateKeyFor($risk->id->toString());
        }

        return $keys;
    }

    /**
     * The risks currently in paragraaf 16, keyed by their repeater state key.
     *
     * @return array<string, string>
     */
    private static function riskOptions(Get $get): array
    {
        $risks = $get('../../risks');

        return is_array($risks) ? self::riskOptionsFor($risks) : [];
    }

    /**
     * Turns the risks repeater state into checkbox options.
     *
     * Keyed by repeater state key, which for an unsaved risk is a temporary id;
     * DpiaMeasureRiskLinker resolves those to real ids after saving.
     *
     * @param array<mixed> $risks
     *
     * @return array<string, string>
     */
    public static function riskOptionsFor(array $risks): array
    {
        $options = [];

        foreach ($risks as $key => $risk) {
            if (!is_string($key) || !is_array($risk)) {
                continue;
            }

            $title = $risk['title'] ?? null;

            if (!is_string($title) || $title === '') {
                continue;
            }

            $options[$key] = $title;
        }

        return $options;
    }

    private static function hasHighResidualRisk(Get $get): bool
    {
        $residualLevel = $get('residual_level');

        // Form state carries the backed value, not the enum; the enum branch is
        // narrowing for static analysis only.
        // @codeCoverageIgnoreStart
        if ($residualLevel instanceof RiskLevel) {
            return $residualLevel === RiskLevel::HIGH;
        }
        // @codeCoverageIgnoreEnd

        return $residualLevel === RiskLevel::HIGH->value;
    }
}
