<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource;

use App\Filament\Infolists\Components\AttachmentFileEntry;
use App\Filament\Infolists\Components\DateEntry;
use App\Filament\Infolists\Components\ExternalLinkEntry;
use App\Filament\Infolists\Components\SelectMultipleEntry;
use Filament\Infolists\Components\Component;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

use function __;

class DocumentResourceInfolist
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
                        ->label(__('document.name')),
                    TextEntry::make('documentType.name')
                        ->label(__('document.type')),
                    DateEntry::make('expires_at')
                        ->label(__('document.expires_at')),
                    DateEntry::make('notify_at')
                        ->label(__('document.notify_at')),
                ]),
            SelectMultipleEntry::make('tags.name')
                ->label(__('tag.model_plural')),
            ExternalLinkEntry::make('location')
                ->label(__('document.location'))
                ->columnSpan(2),
            AttachmentFileEntry::make('media')
                ->label(__('general.attachments'))
                ->columnSpan(2),
        ];
    }
}
