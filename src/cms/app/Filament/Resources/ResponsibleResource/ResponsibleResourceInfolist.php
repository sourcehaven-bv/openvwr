<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource;

use App\Filament\Infolists\Components\AddressRepeatableEntry;
use App\Filament\Infolists\Components\TagsEntry;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

use function __;

class ResponsibleResourceInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns()
            ->schema([
                Section::make()
                    ->schema(self::getSchema()),
            ]);
    }

    /**
     * @return array<Component>
     */
    public static function getSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    TextEntry::make('name')
                        ->label(__('responsible.name')),
                ]),
            TagsEntry::make(),
            AddressRepeatableEntry::make(),
        ];
    }
}
