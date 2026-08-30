<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Filament\Schemas\Schema;
use Filament\Forms\Contracts\HasForms;

class FilamentTestHelper
{
    public static function createTestForm(?HasForms $component = null): Schema
    {
        if ($component === null) {
            $component = LivewireTestHelper::createTestFormComponent();
        }

        return Schema::make($component);
    }
}
