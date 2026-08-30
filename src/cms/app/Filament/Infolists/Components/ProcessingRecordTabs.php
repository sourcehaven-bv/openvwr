<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Closure;
use Filament\Schemas\Components\Tabs;
use Illuminate\Contracts\Support\Htmlable;

class ProcessingRecordTabs extends Tabs
{
    protected string $view = 'filament.infolists.components.processing-record-tabs';

    public static function make(Htmlable|Closure|string|null $label = null): static
    {
        return parent::make($label)
            ->persistTabInQueryString();
    }
}
