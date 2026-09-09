<?php

declare(strict_types=1);

namespace App\Filament\Forms;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;

/**
 * A Livewire component that only serves as a host for a form built outside
 * a request: to read its structure, not to show it.
 */
final class FormHost extends Component implements HasForms
{
    use InteractsWithForms;

    public function render(): string
    {
        return '';
    }
}
