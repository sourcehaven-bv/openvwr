<?php

declare(strict_types=1);

namespace App\Filament\Resources\LookupListResource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use App\Filament\Infolists\Components\ToggleEntry;
use Filament\Infolists\Components\TextEntry;

use function __;

class LookupListResourceInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema(self::getSchema()),
            ]);
    }

    /**
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(): array
    {
        return [
            Grid::make()
                ->schema([
                    TextEntry::make('name')
                        ->label(__('general.name')),
                    ToggleEntry::make('enabled')
                        ->label(__('general.enabled')),
                ]),
        ];
    }
}
