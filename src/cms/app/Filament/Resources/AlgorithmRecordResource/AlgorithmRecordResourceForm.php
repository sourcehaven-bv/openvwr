<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource;

use App\Filament\Forms\Components\ProcessingRecordWizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;

use function __;

class AlgorithmRecordResourceForm
{
    public static function stepsForm(Form $form): Form
    {
        return $form
            ->schema([
                ProcessingRecordWizard::make()
                    ->schema([
                        Step::make(__('algorithm_record.step_processing_name'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getProcessingName()),
                        Step::make(__('algorithm_record.step_responsible_use'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getResponsibleUse()),
                        Step::make(__('algorithm_record.step_mechanics'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getMechanics()),
                        Step::make(__('algorithm_record.step_meta'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getMeta()),
                        Step::make(__('algorithm_record.step_impact'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getImpact()),
                        Step::make(__('algorithm_record.step_validation'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getValidation()),
                        Step::make(__('algorithm_record.step_attachments'))
                            ->schema(AlgorithmRecordResourceFormSchemas::getAttachments()),
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
                Section::make(__('algorithm_record.step_processing_name'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('algorithm_record.step_responsible_use'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getResponsibleUse())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible_use']),
                Section::make(__('algorithm_record.step_mechanics'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getMechanics())
                    ->extraAttributes(['data-onepage-section' => 'step_mechanics']),
                Section::make(__('algorithm_record.step_meta'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getMeta())
                    ->extraAttributes(['data-onepage-section' => 'step_meta']),
                Section::make(__('algorithm_record.step_impact'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getImpact())
                    ->extraAttributes(['data-onepage-section' => 'step_impact']),
                Section::make(__('algorithm_record.step_validation'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getValidation())
                    ->extraAttributes(['data-onepage-section' => 'step_validation']),
                Section::make(__('algorithm_record.step_attachments'))
                    ->schema(AlgorithmRecordResourceFormSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
            ]);
    }
}
