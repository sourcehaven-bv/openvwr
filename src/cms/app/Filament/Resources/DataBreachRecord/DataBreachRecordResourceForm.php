<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;

use function __;

class DataBreachRecordResourceForm
{
    public static function stepsForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                ProcessingRecordWizard::make()
                    ->schema([
                        Step::make(__('data_breach_record.step_name'))
                            ->schema(DataBreachRecordResourceFormSchemas::getName()),
                        Step::make(__('data_breach_record.step_responsible'))
                            ->schema(DataBreachRecordResourceFormSchemas::getResponsible()),
                        Step::make(__('data_breach_record.step_dates'))
                            ->schema(DataBreachRecordResourceFormSchemas::getDates()),
                        Step::make(__('data_breach_record.step_incident'))
                            ->schema(DataBreachRecordResourceFormSchemas::getIncident()),
                        Step::make(__('data_breach_record.step_notification'))
                            ->schema(DataBreachRecordResourceFormSchemas::getNotification()),
                        Step::make(__('data_breach_record.step_processing_records'))
                            ->schema(DataBreachRecordResourceFormSchemas::getProcessingRecords()),
                        Step::make(__('data_breach_record.step_attachments'))
                            ->schema(DataBreachRecordResourceFormSchemas::getAttachments()),
                    ])
                    ->skippable()
                    ->persistStepInQueryString()
                    ->columnSpanFull(),
            ]);
    }

    public static function onePageForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('data_breach_record.step_name'))
                    ->schema(DataBreachRecordResourceFormSchemas::getName())
                    ->extraAttributes(['data-onepage-section' => 'step_name']),
                Section::make(__('data_breach_record.step_responsible'))
                    ->schema(DataBreachRecordResourceFormSchemas::getResponsible())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible']),
                Section::make(__('data_breach_record.step_dates'))
                    ->schema(DataBreachRecordResourceFormSchemas::getDates())
                    ->extraAttributes(['data-onepage-section' => 'step_dates']),
                Section::make(__('data_breach_record.step_incident'))
                    ->schema(DataBreachRecordResourceFormSchemas::getIncident())
                    ->extraAttributes(['data-onepage-section' => 'step_incident']),
                Section::make(__('data_breach_record.step_notification'))
                    ->schema(DataBreachRecordResourceFormSchemas::getNotification())
                    ->extraAttributes(['data-onepage-section' => 'step_notification']),
                Section::make(__('data_breach_record.step_processing_records'))
                    ->schema(DataBreachRecordResourceFormSchemas::getProcessingRecords())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_records']),
                Section::make(__('data_breach_record.step_attachments'))
                    ->schema(DataBreachRecordResourceFormSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
            ]);
    }
}
