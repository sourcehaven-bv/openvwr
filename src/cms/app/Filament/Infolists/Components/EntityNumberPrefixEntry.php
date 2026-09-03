<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Infolists\Components\TextEntry;

use function __;
use function sprintf;

class EntityNumberPrefixEntry extends TextEntry
{
    public static function make(?string $name = null): static
    {
        return parent::make(sprintf('%sEntityNumberCounter.prefix', $name))
            ->label(__(sprintf('organisation.%s_entity_number_prefix', $name)));
    }
}
