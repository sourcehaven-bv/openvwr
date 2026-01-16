<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord;

use App\Filament\Forms\Components\CheckboxList;
use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\SelectMultipleWithLookup;
use App\Filament\Forms\Components\TextInput\EntityNumber;
use App\Filament\Forms\FormHelper;
use App\Filament\Resources\DocumentResource\DocumentResourceForm;
use App\Filament\Resources\ResponsibleResource\ResponsibleResourceForm;
use App\Filament\TenantScoped;
use App\Models\Avg\AvgProcessorProcessingRecord;
use App\Models\Avg\AvgResponsibleProcessingRecord;
use App\Models\Document;
use App\Models\Responsible;
use App\Models\Wpg\WpgProcessingRecord;
use App\Rules\CurrentOrganisation;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Arr;
use Webmozart\Assert\Assert;

use function __;

class DataBreachRecordResourceFormSchemas
{
    /**
     * @return array<Component>
     */
    public static function getName(): array
    {
        $typeOptions = __('data_breach_record.type_options');
        Assert::allString($typeOptions);

        return [
            EntityNumber::make()
                ->label(__('data_breach_record.number')),
            TextInput::make('name')
                ->label(__('data_breach_record.name'))
                ->required()
                ->maxLength(255),
            DatePicker::make('reported_at')
                ->label(__('data_breach_record.reported_at')),
            Radio::make('type')
                ->label(__('data_breach_record.type'))
                ->options(FormHelper::setValueAsKey($typeOptions))
                ->default(Arr::first($typeOptions))
                ->required(),
            Toggle::make('ap_reported')
                ->label(__('data_breach_record.ap_reported')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_name_title'),
                __('information_blocks.data_breach_record.step_name_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getResponsible(): array
    {
        return [
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'responsible_id',
                'responsibles',
                Responsible::class,
                ResponsibleResourceForm::getSchema(),
                'name',
            )
                ->label(__('responsible.model_plural')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_responsible_title'),
                __('information_blocks.data_breach_record.step_responsible_info'),
                __('information_blocks.data_breach_record.step_responsible_extra_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getDates(): array
    {
        return [
            DatePicker::make('discovered_at')
                ->label(__('data_breach_record.discovered_at'))
                ->required(),
            DatePicker::make('started_at')
                ->label(__('data_breach_record.started_at')),
            DatePicker::make('ended_at')
                ->label(__('data_breach_record.ended_at')),
            DatePicker::make('ap_reported_at')
                ->label(__('data_breach_record.ap_reported_at')),
            DatePicker::make('completed_at')
                ->label(__('data_breach_record.completed_at')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_dates_title'),
                __('information_blocks.data_breach_record.step_dates_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getIncident(): array
    {
        $natureOfIncidentOptions = __('data_breach_record.nature_of_incident_options');
        Assert::allString($natureOfIncidentOptions);

        $personalDataCategoriesOptions = __('data_breach_record.personal_data_categories_options');
        Assert::allString($personalDataCategoriesOptions);

        $personalDataSpecialCategoriesOptions = __('data_breach_record.personal_data_special_categories_options');
        Assert::allString($personalDataSpecialCategoriesOptions);

        $reportedToInvolvedCommunicationOptions = __('data_breach_record.reported_to_involved_communication_options');
        Assert::allString($reportedToInvolvedCommunicationOptions);

        return [
            Radio::make('nature_of_incident')
                ->label(__('data_breach_record.nature_of_incident'))
                ->options(FormHelper::setValueAsKey($natureOfIncidentOptions))
                ->live(),
            Textarea::make('nature_of_incident_other')
                ->label(__('data_breach_record.nature_of_incident_other'))
                ->visible(FormHelper::fieldValueEquals(['nature_of_incident' => 'Overig'])),
            Textarea::make('summary')
                ->label(__('data_breach_record.summary'))
                ->required(),
            Textarea::make('involved_people')
                ->label(__('data_breach_record.involved_people'))
                ->required(),
            CheckboxList::makeWithValidatedOptions('personal_data_categories', FormHelper::setValueAsKey($personalDataCategoriesOptions))
                ->label(__('data_breach_record.personal_data_categories'))
                ->live(),
            Textarea::make('personal_data_categories_other')
                ->label(__('data_breach_record.personal_data_categories_other'))
                ->visible(FormHelper::fieldValuesContainValue('personal_data_categories', 'Anders')),
            CheckboxList::makeWithValidatedOptions(
                'personal_data_special_categories',
                FormHelper::setValueAsKey($personalDataSpecialCategoriesOptions),
            )
                ->label(__('data_breach_record.personal_data_special_categories'))
                ->live(),
            Textarea::make('estimated_risk')
                ->label(__('data_breach_record.estimated_risk'))
                ->required(),
            Textarea::make('measures')
                ->label(__('data_breach_record.measures'))
                ->required(),
            Toggle::make('reported_to_involved')
                ->label(__('data_breach_record.reported_to_involved'))
                ->live(),
            CheckboxList::makeWithValidatedOptions(
                'reported_to_involved_communication',
                FormHelper::setValueAsKey($reportedToInvolvedCommunicationOptions),
            )
                ->label(__('data_breach_record.reported_to_involved_communication'))
                ->visible(FormHelper::isFieldEnabled('reported_to_involved'))
                ->live(),
            Textarea::make('reported_to_involved_communication_other')
                ->label(__('data_breach_record.reported_to_involved_communication_other'))
                ->visible(FormHelper::fieldValuesContainValue('reported_to_involved_communication', 'Anders')),
            Toggle::make('fg_reported')
                ->label(__('data_breach_record.fg_reported')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_incident_title'),
                __('information_blocks.data_breach_record.step_incident_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessingRecords(): array
    {
        return [
            Select::make('avgResponsibleProcessingRecords')
                ->label(__('avg_responsible_processing_record.model_plural'))
                ->relationship('avgResponsibleProcessingRecords', 'name', TenantScoped::getAsClosure())
                ->rules([CurrentOrganisation::forModel(AvgResponsibleProcessingRecord::class)])
                ->multiple(),
            Select::make('avgProcessorProcessingRecords')
                ->label(__('avg_processor_processing_record.model_plural'))
                ->relationship('avgProcessorProcessingRecords', 'name', TenantScoped::getAsClosure())
                ->rules([CurrentOrganisation::forModel(AvgProcessorProcessingRecord::class)])
                ->multiple(),
            Select::make('wpgProcessingRecords')
                ->label(__('wpg_processing_record.model_plural'))
                ->relationship('wpgProcessingRecords', 'name', TenantScoped::getAsClosure())
                ->rules([CurrentOrganisation::forModel(WpgProcessingRecord::class)])
                ->multiple(),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_processing_records_title'),
                __('information_blocks.data_breach_record.step_processing_records_info'),
            ),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            SelectMultipleWithLookup::makeForRelationshipWithCreate(
                'document_id',
                'documents',
                Document::class,
                DocumentResourceForm::getSchema(),
                'name',
            )
                ->label(__('document.model_plural')),
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_attachments_title'),
                __('information_blocks.data_breach_record.step_attachments_info'),
            ),
        ];
    }
}
