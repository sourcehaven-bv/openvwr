<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaRecordResource\Pages;

use App\Filament\Resources\DpiaRecordResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListDpiaRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = DpiaRecordResource::class;

    public function getSubheading(): string
    {
        return __('dpia_record.register_description');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modelLabel(__('dpia_record.model_singular')),
        ];
    }
}
