<?php

declare(strict_types=1);

namespace App\Filament\Resources\ReceiverResource;

use App\Filament\Infolists\Components\TagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use function __;

class ReceiverResourceInfolist
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
            TextEntry::make('description')
                ->label(__('receiver.description')),
            TagsEntry::make(),
        ];
    }
}
