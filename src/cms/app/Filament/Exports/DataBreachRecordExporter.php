<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Filament\Actions\Exports\ExportColumn;
use App\Models\DataBreachRecord;

use function __;

class DataBreachRecordExporter extends Exporter
{
    protected static ?string $model = DataBreachRecord::class;

    public static function getColumns(): array
    {
        return [
            ...self::identificationColumns(),
            ...self::responsibleColumns(),
            ...self::datesColumns(),
            ...self::incidentColumns(),
            ...self::notificationColumns(),
            ...self::processingRecordsColumns(),
            ...self::metadataColumns(),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function identificationColumns(): array
    {
        return [
            ExportColumn::make('organisation.name')
                ->label(__('organisation.model_singular')),
            ExportColumn::make('organisation.responsibleLegalEntity.name')
                ->label(__('responsible_legal_entity.model_singular')),
            ExportColumn::make('number')
                ->label(__('data_breach_record.entityNumber.number')),
            ExportColumn::make('name')
                ->label(__('data_breach_record.name')),
            ExportColumn::make('reported_at')
                ->label(__('data_breach_record.reported_at')),
            ExportColumn::make('type')
                ->label(__('data_breach_record.type')),
            ExportColumn::make('ap_reported')
                ->label(__('data_breach_record.ap_reported')),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function responsibleColumns(): array
    {
        return [
            ExportColumn::make('responsibles.name')
                ->label(__('responsible.model_plural')),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function datesColumns(): array
    {
        return [
            ExportColumn::make('discovered_at')
                ->label(__('data_breach_record.discovered_at')),
            ExportColumn::make('started_at')
                ->label(__('data_breach_record.started_at')),
            ExportColumn::make('ended_at')
                ->label(__('data_breach_record.ended_at')),
            ExportColumn::make('ap_reported_at')
                ->label(__('data_breach_record.ap_reported_at')),
            ExportColumn::make('completed_at')
                ->label(__('data_breach_record.completed_at')),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function incidentColumns(): array
    {
        return [
            ExportColumn::make('nature_of_incident')
                ->label(__('data_breach_record.nature_of_incident')),
            ExportColumn::make('nature_of_incident_other')
                ->label(__('data_breach_record.nature_of_incident_other')),
            ExportColumn::make('summary')
                ->label(__('data_breach_record.summary')),
            ExportColumn::make('involved_people')
                ->label(__('data_breach_record.involved_people')),
            ExportColumn::make('personal_data_categories')
                ->label(__('data_breach_record.personal_data_categories')),
            ExportColumn::make('personal_data_categories_other')
                ->label(__('data_breach_record.personal_data_categories_other')),
            ExportColumn::make('personal_data_special_categories')
                ->label(__('data_breach_record.personal_data_special_categories')),
            ExportColumn::make('estimated_risk')
                ->label(__('data_breach_record.estimated_risk')),
            ExportColumn::make('measures')
                ->label(__('data_breach_record.measures')),
            ExportColumn::make('reported_to_involved_communication')
                ->label(__('data_breach_record.reported_to_involved_communication')),
            ExportColumn::make('reported_to_involved_communication_other')
                ->label(__('data_breach_record.reported_to_involved_communication_other')),
            ExportColumn::make('fg_reported')
                ->label(__('data_breach_record.fg_reported')),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function notificationColumns(): array
    {
        return [
            ExportColumn::make('how_discovered')
                ->label(__('data_breach_record.how_discovered')),
            ExportColumn::make('late_notification_reason')
                ->label(__('data_breach_record.late_notification_reason')),
            ExportColumn::make('nature_of_breach')
                ->label(__('data_breach_record.nature_of_breach')),
            ExportColumn::make('record_count')
                ->label(__('data_breach_record.record_count')),
            ExportColumn::make('record_count_explanation')
                ->label(__('data_breach_record.record_count_explanation')),
            ExportColumn::make('affected_groups')
                ->label(__('data_breach_record.affected_groups')),
            ExportColumn::make('affected_groups_other')
                ->label(__('data_breach_record.affected_groups_other')),
            ExportColumn::make('affected_count_known')
                ->label(__('data_breach_record.affected_count_known')),
            ExportColumn::make('affected_count')
                ->label(__('data_breach_record.affected_count')),
            ExportColumn::make('affected_count_min')
                ->label(__('data_breach_record.affected_count_min')),
            ExportColumn::make('affected_count_max')
                ->label(__('data_breach_record.affected_count_max')),
            ExportColumn::make('protection_beforehand')
                ->label(__('data_breach_record.protection_beforehand')),
            ExportColumn::make('protection_beforehand_explanation')
                ->label(__('data_breach_record.protection_beforehand_explanation')),
            ExportColumn::make('consequences_controller')
                ->label(__('data_breach_record.consequences_controller')),
            ExportColumn::make('consequences_controller_other')
                ->label(__('data_breach_record.consequences_controller_other')),
            ExportColumn::make('consequences_data_subjects')
                ->label(__('data_breach_record.consequences_data_subjects')),
            ExportColumn::make('consequences_data_subjects_other')
                ->label(__('data_breach_record.consequences_data_subjects_other')),
            ExportColumn::make('risk_severity')
                ->label(__('data_breach_record.risk_severity')),
            ExportColumn::make('reported_to_involved_count')
                ->label(__('data_breach_record.reported_to_involved_count')),
            ExportColumn::make('other_supervisors')
                ->label(__('data_breach_record.other_supervisors')),
            ExportColumn::make('other_supervisors_other')
                ->label(__('data_breach_record.other_supervisors_other')),
            ExportColumn::make('cross_border')
                ->label(__('data_breach_record.cross_border')),
            ExportColumn::make('cross_border_countries')
                ->label(__('data_breach_record.cross_border_countries')),
            ExportColumn::make('reported_other_dpas')
                ->label(__('data_breach_record.reported_other_dpas')),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function processingRecordsColumns(): array
    {
        return [
            ExportColumn::make('avgResponsibleProcessingRecords.name')
                ->label(__('avg_responsible_processing_record.model_plural')),
            ExportColumn::make('avgProcessorProcessingRecords.name')
                ->label(__('avg_processor_processing_record.model_plural')),
            ExportColumn::make('wpgProcessingRecords.name')
                ->label(__('wpg_processing_record.model_plural')),
        ];
    }

    /**
     * @return array<ExportColumn>
     */
    private static function metadataColumns(): array
    {
        return [
            ExportColumn::make('created_at')
                ->label(__('general.created_at')),
            ExportColumn::make('updated_at')
                ->label(__('general.updated_at')),
        ];
    }
}
