<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaPrescanRecordResource;

use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\TagsInput;
use App\Filament\Forms\Components\TextInput\EntityNumber;
use App\Filament\Forms\FormHelper;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Services\Dpia\PrescanCriteria;
use App\Services\Dpia\PrescanLiveStatus;
use App\Services\Dpia\PrescanOutcomeSummary;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

use function __;
use function e;
use function is_array;
use function is_string;
use function today;

class DpiaPrescanRecordResourceFormSchemas
{
    /**
     * @return array<Component>
     */
    public static function getGeneral(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_prescan_record.step_general_title'),
                __('information_blocks.dpia_prescan_record.step_general_info'),
                __('information_blocks.dpia_prescan_record.step_general_extra_info'),
            ),
            EntityNumber::make(),
            TextInput::make('name')
                ->label(__('dpia_prescan_record.name'))
                ->helperText(__('dpia_prescan_record.help_name'))
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label(__('dpia_prescan_record.description'))
                ->helperText(__('dpia_prescan_record.help_description'))
                ->rows(4)
                ->columnSpanFull(),
            // A pre-scan is filled in on the day it is carried out, so today is
            // the right guess. It stays editable for the case where the scan is
            // entered afterwards.
            DatePicker::make('assessed_at')
                ->label(__('dpia_prescan_record.assessed_at'))
                ->helperText(__('dpia_prescan_record.help_assessed_at'))
                ->default(today()),
            TagsInput::make(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getGrounds(): array
    {
        return [
            Section::make(__('dpia_prescan_record.grounds_heading'))
                ->description(__('dpia_prescan_record.grounds_description'))
                ->schema([
                    Toggle::make('new_legislation')
                        ->label(__('dpia_prescan_record.new_legislation'))
                        ->live(),
                    Toggle::make('departmental_policy')
                        ->label(__('dpia_prescan_record.departmental_policy'))
                        ->live(),
                    Toggle::make('public_cloud')
                        ->label(__('dpia_prescan_record.public_cloud'))
                        ->helperText(__('dpia_prescan_record.help_public_cloud'))
                        ->live(),
                ]),
            self::mandatoryNotice(),
        ];
    }

    /**
     * Tells the invuller, as soon as one of the aanleidingen is ticked, that the
     * DPIA question is settled -- and why the rest of the form still matters.
     *
     * The remaining questions are not hidden at that point. They decide whether
     * a DTIA, KIA or IAMA is also needed, and the criteria that apply are input
     * for the risk assessment in the DPIA itself. Collapsing the form on the
     * first "ja" would throw that away.
     */
    private static function mandatoryNotice(): Component
    {
        return Placeholder::make('dpia_mandatory_notice')
            ->hiddenLabel()
            ->content(new HtmlString(
                '<p class="text-sm text-danger-600 dark:text-danger-400 font-medium">'
                . e(__('dpia_prescan_record.mandatory_notice_title'))
                . '</p><p class="text-sm text-gray-500">'
                . e(__('dpia_prescan_record.mandatory_notice_body'))
                . '</p>',
            ))
            ->visible(static fn (Get $get): bool => self::dpiaAlreadyMandatory($get))
            ->columnSpanFull();
    }

    /**
     * Whether one of the three aanleidingen already makes a DPIA mandatory.
     */
    private static function dpiaAlreadyMandatory(Get $get): bool
    {
        return (bool) $get('new_legislation')
            || (bool) $get('departmental_policy')
            || (bool) $get('public_cloud');
    }

    /**
     * @return array<Component>
     */
    public static function getApCriteria(): array
    {
        return [
            self::mandatoryNotice(),
            CheckboxList::make('ap_criteria')
                ->label(__('dpia_prescan_record.ap_criteria'))
                ->options(PrescanCriteria::apOptions())
                ->descriptions(PrescanCriteria::apDescriptions())
                ->live()
                ->columnSpanFull(),
            // The counting rule applied, instead of stated.
            Placeholder::make('ap_criteria_status')
                ->hiddenLabel()
                ->content(static fn (Get $get): HtmlString => PrescanLiveStatus::apCriteria($get))
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getEdpbCriteria(): array
    {
        return [
            self::mandatoryNotice(),
            CheckboxList::make('edpb_criteria')
                ->label(__('dpia_prescan_record.edpb_criteria'))
                ->options(PrescanCriteria::edpbOptions())
                ->descriptions(PrescanCriteria::edpbDescriptions())
                ->live()
                ->columnSpanFull(),
            Placeholder::make('edpb_criteria_status')
                ->hiddenLabel()
                ->content(static fn (Get $get): HtmlString => PrescanLiveStatus::edpbCriteria($get))
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getTransfer(): array
    {
        return [
            Toggle::make('international_transfer')
                ->label(__('dpia_prescan_record.international_transfer'))
                ->live(),
            Toggle::make('outside_eea')
                ->label(__('dpia_prescan_record.outside_eea'))
                ->live()
                ->visible(FormHelper::isFieldEnabled('international_transfer')),
            Select::make('transfer_mechanism')
                ->label(__('dpia_prescan_record.transfer_mechanism'))
                ->options([
                    'adequaatheidsbesluit' => __('dpia_prescan_record.transfer_mechanism_adequaatheidsbesluit'),
                    'scc' => __('dpia_prescan_record.transfer_mechanism_scc'),
                    'bcr' => __('dpia_prescan_record.transfer_mechanism_bcr'),
                    'overig' => __('dpia_prescan_record.transfer_mechanism_overig'),
                ])
                ->live()
                ->visible(FormHelper::isFieldEnabled('outside_eea')),
            Placeholder::make('transfer_status')
                ->hiddenLabel()
                ->content(static fn (Get $get): ?HtmlString => PrescanLiveStatus::transfer($get))
                ->visible(static fn (Get $get): bool => PrescanLiveStatus::transfer($get) !== null)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getOther(): array
    {
        return [
            Toggle::make('digital_service')
                ->label(__('dpia_prescan_record.digital_service'))
                ->live(),
            Toggle::make('minors')
                ->label(__('dpia_prescan_record.minors'))
                ->live()
                ->visible(FormHelper::isFieldEnabled('digital_service')),
            Toggle::make('algorithm')
                ->label(__('dpia_prescan_record.algorithm'))
                ->live(),
            // Artikel 27 AI-verordening is easier to recognise from examples
            // than from the article text, so the categories are offered as a
            // checklist with the article itself one click away.
            CheckboxList::make('high_risk_ai_categories')
                ->label(__('dpia_prescan_record.high_risk_ai_categories'))
                ->helperText(__('dpia_prescan_record.help_high_risk_ai_categories'))
                ->options(PrescanCriteria::highRiskAiOptions())
                ->descriptions(PrescanCriteria::highRiskAiDescriptions())
                ->hintAction(
                    Action::make('article_27_info')
                        ->label(__('dpia_prescan_record.article_27_label'))
                        ->icon('heroicon-o-information-circle')
                        ->color('gray')
                        ->link()
                        ->modalHeading(__('dpia_prescan_record.article_27_heading'))
                        ->modalContent(self::articleTwentySevenContent())
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel(__('general.close')),
                )
                ->live()
                ->visible(FormHelper::isFieldEnabled('algorithm'))
                ->columnSpanFull(),
            Placeholder::make('high_risk_ai_status')
                ->hiddenLabel()
                ->content(static fn (Get $get): ?HtmlString => PrescanLiveStatus::highRiskAi($get))
                ->visible(static fn (Get $get): bool => PrescanLiveStatus::highRiskAi($get) !== null)
                ->columnSpanFull(),
        ];
    }

    /**
     * What artikel 27 of the AI-verordening asks for, in short.
     */
    private static function articleTwentySevenContent(): HtmlString
    {
        $paragraphs = __('dpia_prescan_record.article_27_body');

        $body = '';

        if (is_array($paragraphs)) {
            foreach ($paragraphs as $paragraph) {
                if (is_string($paragraph)) {
                    $body .= '<p class="text-sm text-gray-950 dark:text-gray-200" '
                        . 'style="margin-bottom:0.75rem;">' . e($paragraph) . '</p>';
                }
            }
        }

        return new HtmlString($body);
    }

    /**
     * The verdicts, recomputed live from the answers.
     *
     * @return array<Component>
     */
    public static function getOutcome(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.dpia_prescan_record.step_outcome_title'),
                __('information_blocks.dpia_prescan_record.step_outcome_info'),
            ),
            Section::make(__('dpia_prescan_record.outcome_heading'))
                ->description(__('dpia_prescan_record.outcome_description'))
                ->schema([
                    Placeholder::make('outcome_preview')
                        ->hiddenLabel()
                        ->content(static function (Get $get): HtmlString {
                            return PrescanOutcomeSummary::render($get);
                        })
                        ->columnSpanFull(),
                ]),
            Textarea::make('outcome_motivation')
                ->label(__('dpia_prescan_record.outcome_motivation'))
                ->helperText(__('dpia_prescan_record.help_outcome_motivation'))
                ->rows(4)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getRelations(): array
    {
        return [
            RelationTable::makeForRelationship(
                'avg_responsible_processing_records',
                'avgResponsibleProcessingRecords',
                AvgResponsibleProcessingRecord::class,
                'name',
                RelationTableColumns::for(AvgResponsibleProcessingRecord::class),
            )
                ->label(__('dpia_record.avg_responsible_processing_records')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            RelationTable::makeForRelationship(
                'document_id',
                'documents',
                Document::class,
                'name',
                RelationTableColumns::for(Document::class),
                DocumentResourceForm::getSchema(),
            )
                ->label(__('document.model_plural')),
        ];
    }
}
