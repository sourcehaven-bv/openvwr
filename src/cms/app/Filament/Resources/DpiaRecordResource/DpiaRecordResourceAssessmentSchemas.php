<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Grid;
use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\Repeater\DpiaMeasuresRepeater;
use App\Filament\Forms\Components\Repeater\DpiaPersonalDataRepeater;
use App\Filament\Forms\Components\Repeater\DpiaRisksRepeater;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\FormHelper;
use App\Services\Dpia\DpiaSectionNotice;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function is_array;

/**
 * Parts C and D of the DPIA -- the risks and the measures -- plus the process
 * steps that hang off them: consultation, FG advice, AP consultation and the
 * review date.
 *
 * These live apart from the descriptive paragraphs (parts A and B in
 * {@see DpiaRecordResourceFormSchemas}) because this is where the DPIA stops
 * describing and starts judging, and because they are the sections that talk
 * to each other: a high residual risk in paragraaf 17 drives the artikel 36
 * warning in the consultation step.
 */
class DpiaRecordResourceAssessmentSchemas
{
    /**
     * 2. Persoonsgegevens
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getPersonalData(): array
    {
        return [
            Placeholder::make('personal_data_intro')
                ->hiddenLabel()
                ->content(__('dpia_record.personal_data_intro'))
                ->columnSpanFull(),
            DpiaPersonalDataRepeater::make()
                ->columnSpanFull(),
            Textarea::make('personal_data_sources')
                ->label(__('dpia_record.personal_data_sources'))
                ->helperText(__('dpia_record.help_personal_data_sources'))
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    /**
     * 3. Gegevensverwerkingen
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getProcessing(): array
    {
        return [
            Textarea::make('processing_description')
                ->label(__('dpia_record.processing_description'))
                ->helperText(__('dpia_record.help_processing_description'))
                ->rows(6)
                ->columnSpanFull(),
        ];
    }

    /**
     * 16. Risico's voor betrokkenen
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getRisks(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_record.part_c_title'),
                __('information_blocks.dpia_record.part_c_info'),
                __('information_blocks.dpia_record.part_c_extra_info'),
            ),
            DpiaRisksRepeater::make()
                ->columnSpanFull(),
            // Live aandachtspunt: which risks no measure addresses yet.
            Placeholder::make('risks_notice')
                ->hiddenLabel()
                ->content(static fn (Get $get): ?HtmlString => DpiaSectionNotice::risks($get))
                ->visible(static fn (Get $get): bool => DpiaSectionNotice::risks($get) !== null)
                ->columnSpanFull(),
            Textarea::make('risks_additional_information')
                ->label(__('dpia_record.risks_additional_information'))
                ->helperText(__('dpia_record.help_risks_additional_information'))
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    /**
     * 17. Maatregelen
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getMeasures(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_record.part_d_title'),
                __('information_blocks.dpia_record.part_d_info'),
                __('information_blocks.dpia_record.part_d_extra_info'),
            ),
            DpiaMeasuresRepeater::make()
                ->columnSpanFull(),
            Placeholder::make('measures_notice')
                ->hiddenLabel()
                ->content(static fn (Get $get): ?HtmlString => DpiaSectionNotice::measures($get))
                ->visible(static fn (Get $get): bool => DpiaSectionNotice::measures($get) !== null)
                ->columnSpanFull(),
            Textarea::make('measures_additional_information')
                ->label(__('dpia_record.measures_additional_information'))
                ->rows(3)
                ->columnSpanFull(),
            Textarea::make('residual_risk_acceptance')
                ->label(__('dpia_record.residual_risk_acceptance'))
                ->helperText(__('dpia_record.help_residual_risk_acceptance'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * Consultatie en advies (proceskader, deel I).
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getConsultation(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_record.step_consultation_title'),
                __('information_blocks.dpia_record.step_consultation_info'),
                __('information_blocks.dpia_record.step_consultation_extra_info'),
            ),
            Toggle::make('data_subjects_consulted')
                ->label(__('dpia_record.data_subjects_consulted'))
                ->helperText(__('dpia_record.help_data_subjects_consulted'))
                ->live(),
            Textarea::make('data_subjects_consultation')
                ->label(__('dpia_record.data_subjects_consultation'))
                ->helperText(__('dpia_record.help_data_subjects_consultation'))
                ->rows(4)
                ->columnSpanFull(),
            Grid::make()
                ->schema([
                    Textarea::make('fg_advice')
                        ->label(__('dpia_record.fg_advice'))
                        ->helperText(__('dpia_record.help_fg_advice'))
                        ->rows(4)
                        ->columnSpanFull(),
                    Textarea::make('fg_advice_followup')
                        ->label(__('dpia_record.fg_advice_followup'))
                        ->rows(3)
                        ->columnSpanFull(),
                    DatePicker::make('fg_advice_received_at')
                        ->label(__('dpia_record.fg_advice_received_at')),
                ]),
            // Warns as soon as a measure leaves a high residual risk, so the
            // artikel 36 obligation surfaces here and not only in paragraaf 17.
            Placeholder::make('ap_consultation_hint')
                ->hiddenLabel()
                ->content(new HtmlString(
                    '<p class="text-sm text-warning-600 dark:text-warning-400">'
                    . e(__('dpia_record.ap_consultation_warning'))
                    . '</p>',
                ))
                ->visible(static fn (Get $get): bool => self::hasHighResidualRisk($get))
                ->columnSpanFull(),
            Toggle::make('ap_consultation_required')
                ->label(__('dpia_record.ap_consultation_required'))
                ->helperText(__('dpia_record.help_ap_consultation_required'))
                ->live(),
            Textarea::make('ap_consultation')
                ->label(__('dpia_record.ap_consultation'))
                ->helperText(__('dpia_record.help_ap_consultation'))
                ->rows(4)
                ->columnSpanFull()
                ->visible(FormHelper::isFieldEnabled('ap_consultation_required')),
            DatePicker::make('ap_consultation_requested_at')
                ->label(__('dpia_record.ap_consultation_requested_at'))
                ->visible(FormHelper::isFieldEnabled('ap_consultation_required')),
        ];
    }

    /**
     * Vaststelling en herziening.
     *
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getReview(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_record.step_review_title'),
                __('information_blocks.dpia_record.step_review_info'),
            ),
            Textarea::make('management_summary')
                ->label(__('dpia_record.management_summary'))
                ->helperText(__('dpia_record.help_management_summary'))
                ->rows(5)
                ->columnSpanFull(),
            Grid::make()
                ->schema([
                    DatePicker::make('assessed_at')
                        ->label(__('dpia_record.assessed_at'))
                        ->helperText(__('dpia_record.help_assessed_at'))
                        ->live(),
                    DatePicker::make('review_at')
                        ->label(__('dpia_record.review_at'))
                        ->helperText(__('dpia_record.help_review_at')),
                ]),
        ];
    }

    /**
     * Whether any measure on the form leaves a high residual risk.
     */
    private static function hasHighResidualRisk(Get $get): bool
    {
        $measures = $get('measures');

        // The repeater always hands over an array of item arrays; both guards
        // are narrowing for static analysis only.
        // @codeCoverageIgnoreStart
        if (!is_array($measures)) {
            return false;
        }
        // @codeCoverageIgnoreEnd

        foreach ($measures as $measure) {
            // @codeCoverageIgnoreStart
            if (!is_array($measure)) {
                continue;
            }
            // @codeCoverageIgnoreEnd

            if (($measure['residual_level'] ?? null) === 'hoog') {
                return true;
            }
        }

        return false;
    }
}
