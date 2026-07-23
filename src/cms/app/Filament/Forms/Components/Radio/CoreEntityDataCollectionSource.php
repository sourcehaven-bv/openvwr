<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components\Radio;

use App\Enums\CoreEntityDataCollectionSource as CoreEntityDataCollectionSourceEnum;
use Filament\Forms\Components\Radio;

use function __;

class CoreEntityDataCollectionSource extends Radio
{
    public static function make(string $name = 'data_collection_source'): static
    {
        return parent::make($name)
            ->label(__('general.data_collection_source'))
            ->helperText(__('general.data_collection_source_help_short'))
            ->hintIcon('heroicon-o-information-circle', __('general.data_collection_source_help'))
            ->options(CoreEntityDataCollectionSourceEnum::class)
            ->enum(CoreEntityDataCollectionSourceEnum::class)
            ->default(CoreEntityDataCollectionSourceEnum::PRIMARY)
            ->required()
            ->inline();
    }
}
