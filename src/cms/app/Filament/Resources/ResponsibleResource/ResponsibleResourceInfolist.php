<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleResource;

use App\Filament\Infolists\Components\AddressRepeatableEntry;
use App\Filament\Infolists\Components\TagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use function __;

class ResponsibleResourceInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns()
            ->components([
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
