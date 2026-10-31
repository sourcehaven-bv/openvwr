<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Closure;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Contracts\Support\Htmlable;

use function __;

class AddressRepeatableEntry extends Section
{
    public static function make(string|array|Htmlable|Closure|null $heading = null): static
    {
        return parent::make()
            ->label(__('address.model_plural'))
            ->schema([
                Section::make(__('address.section_address'))
                    ->columns()
                    ->schema([
                        TextEntry::make('address.address')
                            ->label(__('address.address')),
                        TextEntry::make('address.postal_code')
                            ->label(__('address.postal_code')),
                        TextEntry::make('address.city')
                            ->label(__('address.city')),
                        TextEntry::make('address.country')
                            ->label(__('address.country')),
                    ]),
                Section::make(__('address.section_postbox'))
                    ->columns()
                    ->schema([
                        TextEntry::make('address.postbox')
                            ->label(__('address.postbox')),
                        TextEntry::make('address.postbox_postal_code')
                            ->label(__('address.postbox_postal_code')),
                        TextEntry::make('address.postbox_city')
                            ->label(__('address.postbox_city')),
                        TextEntry::make('address.postbox_country')
                            ->label(__('address.postbox_country')),
                    ]),
            ]);
    }
}
