<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Filament\Infolists\Components\TagsEntry;
use Filament\Infolists\Components\TextEntry;

use function __;

class SystemResourceInfolist
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
     * @return array<\Filament\Schemas\Components\Component>
     */
    public static function getSchema(): array
    {
        return [
            TextEntry::make('description')
                ->label(__('system.description')),
            TagsEntry::make(),
        ];
    }
}
