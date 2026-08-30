<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Config\Feature;
use Filament\Actions\Action;

class GoToPublicPageAction extends Action
{
    public static function make(?string $name = 'go_to_public_page'): static
    {
        return parent::make($name)
            ->name('go_to_public_page')
            ->view('filament.actions.public-page-action')
            ->visible(Feature::publishingEnabled());
    }
}
