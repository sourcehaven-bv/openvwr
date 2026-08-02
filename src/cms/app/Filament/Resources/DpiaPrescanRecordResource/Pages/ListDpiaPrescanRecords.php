<?php

declare(strict_types=1);

namespace App\Filament\Resources\DpiaPrescanRecordResource\Pages;

use App\Filament\Resources\DpiaPrescanRecordResource;
use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

use function __;

class ListDpiaPrescanRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected static string $resource = DpiaPrescanRecordResource::class;

    public function getSubheading(): string
    {
        return __('dpia_prescan_record.register_description');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modelLabel(__('dpia_prescan_record.model_singular')),
        ];
    }
}
