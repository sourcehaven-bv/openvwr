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
            // naam datalek
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
            ExportColumn::make('tags.name')
                ->label(__('tag.model_plural')),
            ExportColumn::make('type')
                ->label(__('data_breach_record.type')),
            ExportColumn::make('ap_reported')
                ->label(__('data_breach_record.ap_reported')),

            // verantwoordelijke
            ExportColumn::make('responsibles.name')
                ->label(__('responsible.model_plural')),

            // dates
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

            // incident
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

            // verwerkingen
            ExportColumn::make('avgResponsibleProcessingRecords.name')
                ->label(__('avg_responsible_processing_record.model_plural')),
            ExportColumn::make('avgProcessorProcessingRecords.name')
                ->label(__('avg_processor_processing_record.model_plural')),
            ExportColumn::make('wpgProcessingRecords.name')
                ->label(__('wpg_processing_record.model_plural')),

            // overig
            ExportColumn::make('created_at')
                ->label(__('general.created_at')),
            ExportColumn::make('updated_at')
                ->label(__('general.updated_at')),
        ];
    }
}
