<?php

declare(strict_types=1);

namespace App\Filament\Resources\SystemResource;

use App\Filament\Infolists\Components\SelectMultipleEntry;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

use function __;

class SystemResourceInfolist
{
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(1)
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
            TextEntry::make('description')
                ->label(__('system.description')),
            SelectMultipleEntry::make('tags.name')
                ->label(__('tag.model_plural')),
        ];
    }
}
