<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource;

use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\Select\SelectSingleWithLookup;
use App\Filament\Forms\Components\TagsInput;
use App\Filament\Forms\Components\TextInput\EntityNumber;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Models\Algorithm\AlgorithmPublicationCategory;
use App\Models\Algorithm\AlgorithmStatus;
use App\Models\Algorithm\AlgorithmTheme;
use App\Models\Document;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

use function __;

class AlgorithmRecordResourceFormSchemas
{
    /**
     * @return array<Component>
     */
    public static function getProcessingName(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_processing_name_title'),
                __('information_blocks.algorithm_record.step_processing_name_info'),
            ),
            EntityNumber::make()
                ->label(__('algorithm_record.number')),
            TextInput::make('name')
                ->label(__('general.name'))
                ->helperText(__('algorithm_record.help_name'))
                ->required()
                ->maxLength(100),
            Textarea::make('description')
                ->label(__('algorithm_record.description'))
                ->helperText(__('algorithm_record.help_description'))
                ->maxLength(400),
            TagsInput::make(),
            SelectSingleWithLookup::makeWithDisabledOptions('algorithm_theme_id', 'algorithmTheme', AlgorithmTheme::class, 'name')
                ->label(__('algorithm_record.theme'))
                ->helperText(__('algorithm_record.help_theme')),
            SelectSingleWithLookup::makeWithDisabledOptions('algorithm_status_id', 'algorithmStatus', AlgorithmStatus::class, 'name')
                ->label(__('algorithm_record.status'))
                ->helperText(__('algorithm_record.help_status')),
            DatePicker::make('start_date')
                ->label(__('algorithm_record.start_date'))
                ->helperText(__('algorithm_record.help_start_date')),
            DatePicker::make('end_date')
                ->label(__('algorithm_record.end_date'))
                ->helperText(__('algorithm_record.help_end_date')),
            TextInput::make('contact_data')
                ->label(__('algorithm_record.contact_data'))
                ->helperText(__('algorithm_record.help_contact_data'))
                ->maxLength(500),
            TextInput::make('public_page_link')
                ->label(__('algorithm_record.public_page_link'))
                ->helperText(__('algorithm_record.help_public_page_link'))
                ->url()
                ->maxLength(500),
            SelectSingleWithLookup::makeWithDisabledOptions(
                'algorithm_publication_category_id',
                'algorithmPublicationCategory',
                AlgorithmPublicationCategory::class,
                'name',
            )
                ->label(__('algorithm_record.publication_category'))
                ->helperText(__('algorithm_record.help_publication_category')),
            TextInput::make('source_link')
                ->label(__('algorithm_record.source_link'))
                ->helperText(__('algorithm_record.help_source_link'))
                ->columns(1)
                ->url()
                ->maxLength(500),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getResponsibleUse(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_responsible_use_title'),
                __('information_blocks.algorithm_record.step_responsible_use_info'),
            ),
            Textarea::make('resp_goal_and_impact')
                ->label(__('algorithm_record.resp_goal_and_impact'))
                ->maxLength(2500),
            Textarea::make('resp_considerations')
                ->label(__('algorithm_record.resp_considerations'))
                ->maxLength(2500),
            Textarea::make('resp_human_intervention')
                ->label(__('algorithm_record.resp_human_intervention'))
                ->maxLength(2500),
            Textarea::make('resp_risk_analysis')
                ->label(__('algorithm_record.resp_risk_analysis'))
                ->maxLength(2500),
            TextInput::make('resp_legal_base_title')
                ->label(__('algorithm_record.resp_legal_base_title'))
                ->maxLength(100),
            Textarea::make('resp_legal_base')
                ->label(__('algorithm_record.resp_legal_base'))
                ->maxLength(2500),
            TextInput::make('resp_legal_base_link')
                ->label(__('algorithm_record.resp_legal_base_link'))
                ->url()
                ->maxLength(500),
            TextInput::make('resp_processor_registry_link')
                ->label(__('algorithm_record.resp_processor_registry_link'))
                ->url()
                ->maxLength(500),
            Textarea::make('resp_impact_tests')
                ->label(__('algorithm_record.resp_impact_tests'))
                ->maxLength(2500),
            Textarea::make('resp_impact_test_links')
                ->label(__('algorithm_record.resp_impact_test_links'))
                ->maxLength(500),
            Textarea::make('resp_impact_tests_description')
                ->label(__('algorithm_record.resp_impact_tests_description'))
                ->maxLength(2500),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getMechanics(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_mechanics_title'),
                __('information_blocks.algorithm_record.step_mechanics_info'),
            ),
            TextInput::make('oper_data_title')
                ->label(__('algorithm_record.oper_data_title'))
                ->maxLength(500),
            Textarea::make('oper_data')
                ->label(__('algorithm_record.oper_data'))
                ->maxLength(2500),
            Textarea::make('oper_links')
                ->label(__('algorithm_record.oper_links'))
                ->maxLength(500),
            Textarea::make('oper_technical_operation')
                ->label(__('algorithm_record.oper_technical_operation'))
                ->maxLength(5000),
            Textarea::make('oper_supplier')
                ->label(__('algorithm_record.oper_supplier'))
                ->helperText(__('algorithm_record.help_oper_supplier'))
                ->maxLength(200),
            TextInput::make('oper_source_code_link')
                ->label(__('algorithm_record.oper_source_code_link'))
                ->url()
                ->maxLength(500),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getMeta(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_meta_title'),
                __('information_blocks.algorithm_record.step_meta_info'),
            ),
            // 'meta_lang' en 'meta_schema' zijn bewust geen invulvelden: de publicatiestandaard
            // legt deze vast op respectievelijk 'nld' en de versie van de standaard zelf. De
            // aanlever-API van het Algoritmeregister vult ze server-side in.
            DatePicker::make('meta_date_of_development')
                ->label(__('algorithm_record.meta_date_of_development'))
                ->helperText(__('algorithm_record.help_meta_date_of_development')),
            TextInput::make('meta_owner_algorithm')
                ->label(__('algorithm_record.meta_owner_algorithm'))
                ->helperText(__('algorithm_record.help_meta_owner_algorithm'))
                ->required(),
            TextInput::make('meta_product_owner_algorithm')
                ->label(__('algorithm_record.meta_product_owner_algorithm'))
                ->helperText(__('algorithm_record.help_meta_product_owner_algorithm'))
                ->required(),
            TextInput::make('meta_national_id')
                ->label(__('algorithm_record.meta_national_id')),
            TextInput::make('meta_source_id')
                ->label(__('algorithm_record.meta_source_id'))
                ->helperText(__('algorithm_record.help_meta_source_id'))
                ->maxLength(100),
            Textarea::make('meta_tags')
                ->label(__('algorithm_record.meta_tags'))
                ->helperText(__('algorithm_record.help_meta_tags'))
                ->maxLength(2500),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getImpact(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_impact_title'),
                __('information_blocks.algorithm_record.step_impact_info'),
            ),
            self::makeRadio('impact_with_consequences'),
            self::makeRadio('impact_more_algorithms_applied'),
            self::makeRadio('impact_effect_on_outcome'),

            Placeholder::make('impactvol_algorithm_message')
                ->hiddenLabel()
                ->content(new HtmlString(
                    '<div class="rounded-md bg-warning-50 p-4 text-sm font-medium text-warning-800">'
                    . __('algorithm_record.impact_algorithm_message')
                    . '</div>',
                ))
                ->visible(static fn (Get $get): bool =>
                    self::isYes($get('impact_with_consequences'))
                    && self::isYes($get('impact_more_algorithms_applied'))
                    && self::isYes($get('impact_effect_on_outcome'))),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getValidation(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_validation_title'),
                __('information_blocks.algorithm_record.step_validation_info'),
            ),
            self::makeRadio('validation_answers_checked_by_product_owner'),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_attachments_title'),
                __('information_blocks.algorithm_record.step_attachments_info'),
            ),
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

    private static function makeRadio(string $name): Radio
    {
        return Radio::make($name)
            ->label(__('algorithm_record.' . $name))
            ->helperText(__('algorithm_record.help_' . $name))
            ->options([
                '1' => __('general.yes'),
                '0' => __('general.no'),
            ])
            ->live()
            ->nullable();
    }

    private static function isYes(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }
}
