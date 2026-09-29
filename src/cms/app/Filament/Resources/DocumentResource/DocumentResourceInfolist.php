<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource;

use App\Filament\Infolists\Components\AttachmentFileEntry;
use App\Filament\Infolists\Components\DateEntry;
use App\Filament\Infolists\Components\ExternalLinkEntry;
use App\Filament\Infolists\Components\TagsEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use function __;

class DocumentResourceInfolist
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
                        ->label(__('document.name')),
                    TextEntry::make('documentType.name')
                        ->label(__('document.type')),
                    DateEntry::make('expires_at')
                        ->label(__('document.expires_at')),
                    DateEntry::make('notify_at')
                        ->label(__('document.notify_at')),
                ]),
            TagsEntry::make(),
            ExternalLinkEntry::make('location')
                ->label(__('document.location'))
                ->columnSpan(2),
            AttachmentFileEntry::make('media')
                ->label(__('general.attachments'))
                ->columnSpan(2),
        ];
    }
}
