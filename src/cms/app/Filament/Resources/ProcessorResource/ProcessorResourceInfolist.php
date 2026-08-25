<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProcessorResource;

use App\Filament\Infolists\Components\AddressRepeatableEntry;
use App\Filament\Infolists\Components\TagsEntry;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

use function __;

class ProcessorResourceInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
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
                        ->label(__('processor.name')),
                    TextEntry::make('email')
                        ->label(__('processor.email')),
                    TextEntry::make('phone')
                        ->label(__('processor.phone')),
                ]),
            TagsEntry::make(),
            AddressRepeatableEntry::make(),
        ];
    }
}
