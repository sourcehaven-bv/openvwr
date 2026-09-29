<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

class LivewireTestHelper
{
    public static function createTestFormComponent(): Component
    {
        return new class extends Component implements HasForms
        {
            use InteractsWithActions;
            use InteractsWithForms;
        };
    }
}
