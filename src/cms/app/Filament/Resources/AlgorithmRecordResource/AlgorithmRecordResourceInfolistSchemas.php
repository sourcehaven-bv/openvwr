<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource;

use App\Filament\Infolists\Components\DateEntry;
use App\Filament\Infolists\Components\EntityNumberEntry;
use App\Filament\Infolists\Components\ParentSelectEntry;
use App\Filament\Infolists\Components\Section\InformationBlockSection;
use App\Filament\Infolists\Components\SelectMultipleEntry;
use App\Filament\Infolists\Components\TextareaEntry;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\TextEntry;

use function __;

class AlgorithmRecordResourceInfolistSchemas
{
    /**
     * @return array<Component>
     */
    public static function getProcessingName(): array
    {
        return [
            EntityNumberEntry::make(),
            TextEntry::make('name')
                ->label(__('general.name')),
            TextareaEntry::make('description')
                ->label(__('algorithm_record.description')),
            TextEntry::make('algorithmTheme.name')
                ->label(__('algorithm_record.theme')),
            TextEntry::make('algorithmStatus.name')
                ->label(__('algorithm_record.status')),
            DateEntry::make('start_date')
                ->label(__('algorithm_record.start_date')),
            DateEntry::make('end_date')
                ->label(__('algorithm_record.end_date')),
            TextEntry::make('contact_data')
                ->label(__('algorithm_record.contact_data')),
            TextEntry::make('public_page_link')
                ->label(__('algorithm_record.public_page_link')),
            TextEntry::make('algorithmPublicationCategory.name')
                ->label(__('algorithm_record.publication_category')),
            TextEntry::make('source_link')
                ->label(__('algorithm_record.source_link')),
            ParentSelectEntry::make(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_processing_name_title'),
                __('information_blocks.algorithm_record.step_processing_name_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getResponsibleUse(): array
    {
        return [
            TextareaEntry::make('resp_goal_and_impact')
                ->label(__('algorithm_record.resp_goal_and_impact')),
            TextareaEntry::make('resp_considerations')
                ->label(__('algorithm_record.resp_considerations')),
            TextareaEntry::make('resp_human_intervention')
                ->label(__('algorithm_record.resp_human_intervention')),
            TextareaEntry::make('resp_risk_analysis')
                ->label(__('algorithm_record.resp_risk_analysis')),
            TextareaEntry::make('resp_legal_base')
                ->label(__('algorithm_record.resp_legal_base')),
            TextEntry::make('resp_legal_base_link')
                ->label(__('algorithm_record.resp_legal_base_link')),
            TextEntry::make('resp_processor_registry_link')
                ->label(__('algorithm_record.resp_processor_registry_link')),
            TextareaEntry::make('resp_impact_tests')
                ->label(__('algorithm_record.resp_impact_tests')),
            TextareaEntry::make('resp_impact_test_links')
                ->label(__('algorithm_record.resp_impact_test_links')),
            TextareaEntry::make('resp_impact_tests_description')
                ->label(__('algorithm_record.resp_impact_tests_description')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_responsible_use_title'),
                __('information_blocks.algorithm_record.step_responsible_use_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getMechanics(): array
    {
        return [
            TextareaEntry::make('oper_data')
                ->label(__('algorithm_record.oper_data')),
            TextareaEntry::make('oper_links')
                ->label(__('algorithm_record.oper_links')),
            TextareaEntry::make('oper_technical_operation')
                ->label(__('algorithm_record.oper_technical_operation')),
            TextareaEntry::make('oper_supplier')
                ->label(__('algorithm_record.oper_supplier')),
            TextEntry::make('oper_source_code_link')
                ->label(__('algorithm_record.oper_source_code_link')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_mechanics_title'),
                __('information_blocks.algorithm_record.step_mechanics_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getMeta(): array
    {
        return [
            TextEntry::make('meta_lang')
                ->label(__('algorithm_record.meta_lang')),
            TextEntry::make('algorithmMetaSchema.name')
                ->label(__('algorithm_record.meta_schema')),
            TextEntry::make('meta_national_id')
                ->label(__('algorithm_record.meta_national_id')),
            TextEntry::make('meta_source_id')
                ->label(__('algorithm_record.meta_source_id')),
            TextareaEntry::make('meta_tags')
                ->label(__('algorithm_record.meta_tags')),
            DateEntry::make('meta_date_of_development')
                ->label(__('algorithm_record.meta_date_of_development')),
            TextEntry::make('meta_owner_algorithm')
                ->label(__('algorithm_record.meta_owner_algorithm')),
            TextEntry::make('meta_product_owner_algorithm')
                ->label(__('algorithm_record.meta_product_owner_algorithm')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_meta_title'),
                __('information_blocks.algorithm_record.step_meta_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getImpact(): array
    {
        return [
            self::makeTextEntry('impact_with_consequences'),
            self::makeTextEntry('impact_more_algorithms_applied'),
            self::makeTextEntry('impact_effect_on_outcome'),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_impact_title'),
                __('information_blocks.algorithm_record.step_impact_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getValidation(): array
    {
        return [
            self::makeTextEntry('validation_answers_checked_by_product_owner'),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_validation_title'),
                __('information_blocks.algorithm_record.step_validation_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            SelectMultipleEntry::make('documents.name')
                ->label(__('document.model_plural')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.algorithm_record.step_attachments_title'),
                __('information_blocks.algorithm_record.step_attachments_info'),
            ),
        ];
    }

    private static function makeTextEntry(string $name): TextEntry
    {
        return TextEntry::make($name)
            ->label(__('algorithm_record.' . $name))
            ->formatStateUsing(static fn (?bool $state): ?string => match ($state) {
                true => (string) __('general.yes'),
                false => (string) __('general.no'),
                null => null,
            });
    }
}
