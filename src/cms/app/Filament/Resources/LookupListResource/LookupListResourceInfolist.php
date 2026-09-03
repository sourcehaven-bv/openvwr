<?php

declare(strict_types=1);

namespace App\Filament\Resources\LookupListResource;

use App\Filament\Infolists\Components\ToggleEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
     * @return array<Component>
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
