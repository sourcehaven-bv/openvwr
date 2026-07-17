<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Concerns\PersistsFiltersInSession;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

use function __;

abstract class ListLookupListRecords extends ListRecords
{
    use PersistsFiltersInSession;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('all')
                ->label(__('general.all')),
            'enabled' => Tab::make()
                ->label(__('general.enabled'))
                ->query(static function (Builder $query) {
                    return $query->where('enabled', true);
                }),
            'disabled' => Tab::make()
                ->label(__('general.disabled'))
                ->query(static function (Builder $query) {
                    return $query->where('enabled', false);
                }),
        ];
    }
}
