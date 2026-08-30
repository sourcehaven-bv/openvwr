<?php

declare(strict_types=1);

namespace App\Filament\Resources\AlgorithmRecordResource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use App\Filament\Infolists\Components\ProcessingRecordTabs;

use function __;

class AlgorithmRecordResourceInfolist
{
    public static function stepsInfolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->extraAttributes(['class' => 'vertical'])
            ->components([
                ProcessingRecordTabs::make()
                    ->tabs([
                        Tab::make(__('algorithm_record.step_processing_name'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getProcessingName()),
                        Tab::make(__('algorithm_record.step_responsible_use'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getResponsibleUse()),
                        Tab::make(__('algorithm_record.step_mechanics'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getMechanics()),
                        Tab::make(__('algorithm_record.step_meta'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getMeta()),
                        Tab::make(__('algorithm_record.step_impact'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getImpact()),
                        Tab::make(__('algorithm_record.step_validation'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getValidation()),
                        Tab::make(__('algorithm_record.step_attachments'))
                            ->schema(AlgorithmRecordResourceInfolistSchemas::getAttachments()),
                    ]),
            ]);
    }

    public static function onePageInfolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('algorithm_record.step_processing_name'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getProcessingName())
                    ->extraAttributes(['data-onepage-section' => 'step_processing_name']),
                Section::make(__('algorithm_record.step_responsible_use'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getResponsibleUse())
                    ->extraAttributes(['data-onepage-section' => 'step_responsible_use']),
                Section::make(__('algorithm_record.step_mechanics'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getMechanics())
                    ->extraAttributes(['data-onepage-section' => 'step_mechanics']),
                Section::make(__('algorithm_record.step_meta'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getMeta())
                    ->extraAttributes(['data-onepage-section' => 'step_meta']),
                Section::make(__('algorithm_record.step_impact'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getImpact())
                    ->extraAttributes(['data-onepage-section' => 'step_impact']),
                Section::make(__('algorithm_record.step_validation'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getValidation())
                    ->extraAttributes(['data-onepage-section' => 'step_validation']),
                Section::make(__('algorithm_record.step_attachments'))
                    ->schema(AlgorithmRecordResourceInfolistSchemas::getAttachments())
                    ->extraAttributes(['data-onepage-section' => 'step_attachments']),
            ]);
    }
}
