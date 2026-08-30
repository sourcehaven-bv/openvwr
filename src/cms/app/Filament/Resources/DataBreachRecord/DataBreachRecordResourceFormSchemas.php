<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord;

use App\Config\Feature;
use App\Filament\Forms\Components\CheckboxList;
use App\Filament\Forms\Components\DatePicker\DatePicker;
use App\Filament\Forms\Components\RelationTable;
use App\Filament\Forms\Components\RelationTableColumns;
use App\Filament\Forms\Components\Section\InformationBlockSection;
use App\Filament\Forms\Components\TagsInput;
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
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_name_title'),
                __('information_blocks.data_breach_record.step_name_info'),
            ),
            EntityNumber::make()
                ->label(__('data_breach_record.number')),
            TextInput::make('name')
                ->label(__('data_breach_record.name'))
                ->helperText(__('data_breach_record.help_name'))
                ->required()
                ->maxLength(255),
            TagsInput::make(),
            DatePicker::make('reported_at')
                ->label(__('data_breach_record.reported_at'))
                ->helperText(__('data_breach_record.help_reported_at')),
            Radio::make('type')
                ->label(__('data_breach_record.type'))
                ->helperText(__('data_breach_record.help_type'))
                ->options(FormHelper::setValueAsKey($typeOptions))
                ->default(Arr::first($typeOptions))
                ->required(),
            Toggle::make('ap_reported')
                ->label(__('data_breach_record.ap_reported'))
                ->helperText(__('data_breach_record.help_ap_reported')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getResponsible(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_responsible_title'),
                __('information_blocks.data_breach_record.step_responsible_info'),
                __('information_blocks.data_breach_record.step_responsible_extra_info'),
            ),
            RelationTable::makeForRelationship(
                'responsible_id',
                'responsibles',
                Responsible::class,
                'name',
                RelationTableColumns::for(Responsible::class),
                ResponsibleResourceForm::getSchema(),
            )
                ->label(__('responsible.model_plural')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getDates(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_dates_title'),
                __('information_blocks.data_breach_record.step_dates_info'),
            ),
            DatePicker::make('discovered_at')
                ->label(__('data_breach_record.discovered_at'))
                ->helperText(__('data_breach_record.help_discovered_at'))
                ->required(),
            DatePicker::make('started_at')
                ->label(__('data_breach_record.started_at'))
                ->helperText(__('data_breach_record.help_started_at')),
            DatePicker::make('ended_at')
                ->label(__('data_breach_record.ended_at'))
                ->helperText(__('data_breach_record.help_ended_at')),
            DatePicker::make('ap_reported_at')
                ->label(__('data_breach_record.ap_reported_at'))
                ->helperText(__('data_breach_record.help_ap_reported_at')),
            DatePicker::make('completed_at')
                ->label(__('data_breach_record.completed_at'))
                ->helperText(__('data_breach_record.help_completed_at')),
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
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_incident_title'),
                __('information_blocks.data_breach_record.step_incident_info'),
            ),
            Radio::make('nature_of_incident')
                ->label(__('data_breach_record.nature_of_incident'))
                ->helperText(__('data_breach_record.help_nature_of_incident'))
                ->options(FormHelper::setValueAsKey($natureOfIncidentOptions))
                ->live(),
            Textarea::make('nature_of_incident_other')
                ->label(__('data_breach_record.nature_of_incident_other'))
                ->visible(FormHelper::fieldValueEquals(['nature_of_incident' => 'Overig'])),
            Textarea::make('summary')
                ->label(__('data_breach_record.summary'))
                ->helperText(__('data_breach_record.help_summary'))
                ->required(),
            Textarea::make('involved_people')
                ->label(__('data_breach_record.involved_people'))
                ->helperText(__('data_breach_record.help_involved_people'))
                ->required(),
            CheckboxList::makeWithValidatedOptions('personal_data_categories', FormHelper::setValueAsKey($personalDataCategoriesOptions))
                ->label(__('data_breach_record.personal_data_categories'))
                ->helperText(__('data_breach_record.help_personal_data_categories'))
                ->live(),
            Textarea::make('personal_data_categories_other')
                ->label(__('data_breach_record.personal_data_categories_other'))
                ->visible(FormHelper::fieldValuesContainValue('personal_data_categories', 'Anders')),
            CheckboxList::makeWithValidatedOptions(
                'personal_data_special_categories',
                FormHelper::setValueAsKey($personalDataSpecialCategoriesOptions),
            )
                ->label(__('data_breach_record.personal_data_special_categories'))
                ->helperText(__('data_breach_record.help_personal_data_special_categories'))
                ->live(),
            Textarea::make('estimated_risk')
                ->label(__('data_breach_record.estimated_risk'))
                ->helperText(__('data_breach_record.help_estimated_risk'))
                ->required(),
            Textarea::make('measures')
                ->label(__('data_breach_record.measures'))
                ->helperText(__('data_breach_record.help_measures'))
                ->required(),
            Toggle::make('reported_to_involved')
                ->label(__('data_breach_record.reported_to_involved'))
                ->helperText(__('data_breach_record.help_reported_to_involved'))
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
                ->label(__('data_breach_record.fg_reported'))
                ->helperText(__('data_breach_record.help_fg_reported')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getNotification(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_notification_title'),
                __('information_blocks.data_breach_record.step_notification_info'),
            ),
            ...self::getNotificationBreach(),
            ...self::getNotificationAffectedPeople(),
            ...self::getNotificationConsequences(),
            ...self::getNotificationOtherAuthorities(),
        ];
    }

    /**
     * Questions 4 and 5 of the AP form: how the breach came to light and what
     * kind of breach it was.
     *
     * @return array<Component>
     */
    private static function getNotificationBreach(): array
    {
        $natureOfBreachOptions = __('data_breach_record.nature_of_breach_options');
        Assert::allString($natureOfBreachOptions);

        return [
            Textarea::make('how_discovered')
                ->label(__('data_breach_record.how_discovered'))
                ->helperText(__('data_breach_record.help_how_discovered')),
            Textarea::make('late_notification_reason')
                ->label(__('data_breach_record.late_notification_reason'))
                ->helperText(__('data_breach_record.help_late_notification_reason')),
            CheckboxList::makeWithValidatedOptions('nature_of_breach', FormHelper::setValueAsKey($natureOfBreachOptions))
                ->label(__('data_breach_record.nature_of_breach'))
                ->helperText(__('data_breach_record.help_nature_of_breach')),
            TextInput::make('record_count')
                ->label(__('data_breach_record.record_count'))
                ->helperText(__('data_breach_record.help_record_count'))
                ->maxLength(255),
            Textarea::make('record_count_explanation')
                ->label(__('data_breach_record.record_count_explanation')),
        ];
    }

    /**
     * Question 7 of the AP form: who was hit, and how many of them.
     *
     * @return array<Component>
     */
    private static function getNotificationAffectedPeople(): array
    {
        $affectedGroupsOptions = __('data_breach_record.affected_groups_options');
        Assert::allString($affectedGroupsOptions);

        return [
            CheckboxList::makeWithValidatedOptions('affected_groups', FormHelper::setValueAsKey($affectedGroupsOptions))
                ->label(__('data_breach_record.affected_groups'))
                ->live(),
            Textarea::make('affected_groups_other')
                ->label(__('data_breach_record.affected_groups_other'))
                ->visible(FormHelper::fieldValuesContainValue('affected_groups', 'Anders')),
            Toggle::make('affected_count_known')
                ->label(__('data_breach_record.affected_count_known'))
                ->helperText(__('data_breach_record.help_affected_count_known'))
                ->live(),
            TextInput::make('affected_count')
                ->label(__('data_breach_record.affected_count'))
                ->numeric()
                ->minValue(0)
                ->visible(FormHelper::isFieldEnabled('affected_count_known')),
            TextInput::make('affected_count_min')
                ->label(__('data_breach_record.affected_count_min'))
                ->numeric()
                ->minValue(0)
                ->visible(FormHelper::isFieldDisabled('affected_count_known')),
            TextInput::make('affected_count_max')
                ->label(__('data_breach_record.affected_count_max'))
                ->numeric()
                ->minValue(0)
                ->visible(FormHelper::isFieldDisabled('affected_count_known')),
        ];
    }

    /**
     * Questions 8, 9 and 10 of the AP form: what protected the data beforehand,
     * what the fallout is and how severe that is.
     *
     * @return array<Component>
     */
    private static function getNotificationConsequences(): array
    {
        $protectionBeforehandOptions = __('data_breach_record.protection_beforehand_options');
        Assert::allString($protectionBeforehandOptions);

        $consequencesControllerOptions = __('data_breach_record.consequences_controller_options');
        Assert::allString($consequencesControllerOptions);

        $consequencesDataSubjectsOptions = __('data_breach_record.consequences_data_subjects_options');
        Assert::allString($consequencesDataSubjectsOptions);

        $riskSeverityOptions = __('data_breach_record.risk_severity_options');
        Assert::allString($riskSeverityOptions);

        return [
            CheckboxList::makeWithValidatedOptions(
                'protection_beforehand',
                FormHelper::setValueAsKey($protectionBeforehandOptions),
            )
                ->label(__('data_breach_record.protection_beforehand'))
                ->helperText(__('data_breach_record.help_protection_beforehand')),
            Textarea::make('protection_beforehand_explanation')
                ->label(__('data_breach_record.protection_beforehand_explanation')),
            CheckboxList::makeWithValidatedOptions(
                'consequences_controller',
                FormHelper::setValueAsKey($consequencesControllerOptions),
            )
                ->label(__('data_breach_record.consequences_controller'))
                ->live(),
            Textarea::make('consequences_controller_other')
                ->label(__('data_breach_record.consequences_controller_other'))
                ->visible(FormHelper::fieldValuesContainValue('consequences_controller', 'Anders')),
            CheckboxList::makeWithValidatedOptions(
                'consequences_data_subjects',
                FormHelper::setValueAsKey($consequencesDataSubjectsOptions),
            )
                ->label(__('data_breach_record.consequences_data_subjects'))
                ->live(),
            Textarea::make('consequences_data_subjects_other')
                ->label(__('data_breach_record.consequences_data_subjects_other'))
                ->visible(FormHelper::fieldValuesContainValue('consequences_data_subjects', 'Anders')),
            Radio::make('risk_severity')
                ->label(__('data_breach_record.risk_severity'))
                ->helperText(__('data_breach_record.help_risk_severity'))
                ->options(FormHelper::setValueAsKey($riskSeverityOptions)),
            TextInput::make('reported_to_involved_count')
                ->label(__('data_breach_record.reported_to_involved_count'))
                ->numeric()
                ->minValue(0),
        ];
    }

    /**
     * Questions 1.3 and 2 of the AP form: other supervisors and the countries
     * this breach reaches into.
     *
     * @return array<Component>
     */
    private static function getNotificationOtherAuthorities(): array
    {
        $otherSupervisorsOptions = __('data_breach_record.other_supervisors_options');
        Assert::allString($otherSupervisorsOptions);

        return [
            CheckboxList::makeWithValidatedOptions(
                'other_supervisors',
                FormHelper::setValueAsKey($otherSupervisorsOptions),
            )
                ->label(__('data_breach_record.other_supervisors'))
                ->helperText(__('data_breach_record.help_other_supervisors'))
                ->live(),
            Textarea::make('other_supervisors_other')
                ->label(__('data_breach_record.other_supervisors_other'))
                ->visible(FormHelper::fieldValuesContainValue('other_supervisors', 'Andere toezichthouder')),
            Toggle::make('cross_border')
                ->label(__('data_breach_record.cross_border'))
                ->helperText(__('data_breach_record.help_cross_border'))
                ->live(),
            Textarea::make('cross_border_countries')
                ->label(__('data_breach_record.cross_border_countries'))
                ->visible(FormHelper::isFieldEnabled('cross_border')),
            Textarea::make('reported_other_dpas')
                ->label(__('data_breach_record.reported_other_dpas'))
                ->visible(FormHelper::isFieldEnabled('cross_border')),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getProcessingRecords(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_processing_records_title'),
                __('information_blocks.data_breach_record.step_processing_records_info'),
            ),
            Select::make('avgResponsibleProcessingRecords')
                ->label(__('avg_responsible_processing_record.model_plural'))
                ->helperText(__('data_breach_record.help_linked_processing_records'))
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
                ->multiple()
                ->visible(Feature::wpgEnabled()),
        ];
    }

    /**
     * @return array<Component>
     */
    public static function getAttachments(): array
    {
        return [
            InformationBlockSection::makeCollapsible(
                __('information_blocks.data_breach_record.step_attachments_title'),
                __('information_blocks.data_breach_record.step_attachments_info'),
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
}
