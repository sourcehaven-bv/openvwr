<?php

declare(strict_types=1);

namespace App\Filament\Resources\DataBreachRecord;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

use function __;

class DataBreachRecordResourceForm
{
    public static function stepsForm(Form $form): Form
    {
        return $form
            ->schema([
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

    public static function onePageForm(Form $form): Form
    {
        return $form
            ->schema([
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
                Section::make(__('data_breach_record.step_processing_records'))
                    ->schema(DataBreachRecordResourceFormSchemas::getProcessingRecords())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_records']),
                Section::make(__('data_breach_record.step_attachments'))
                    ->schema(DataBreachRecordResourceFormSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
            ]);
    }
}
