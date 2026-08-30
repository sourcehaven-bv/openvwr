<?php

declare(strict_types=1);

namespace App\Filament\Infolists\Components;

use Filament\Schemas\Components\Tabs;

class ProcessingRecordTabs extends Tabs
{
    protected string $view = 'filament.infolists.components.processing-record-tabs';

    public static function make(?string $label = null): static
    {
        return parent::make($label)
            ->persistTabInQueryString();
    }
}
