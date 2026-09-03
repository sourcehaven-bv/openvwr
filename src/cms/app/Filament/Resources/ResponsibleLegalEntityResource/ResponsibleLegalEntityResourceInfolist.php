<?php

declare(strict_types=1);

namespace App\Filament\Resources\ResponsibleLegalEntityResource;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use function __;

class ResponsibleLegalEntityResourceInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
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
            TextEntry::make('name')
                ->label(__('tag.name')),
        ];
    }
}
