<?php

declare(strict_types=1);

namespace App\Filament;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Vite;

class SimpleAvatarProvider implements AvatarProvider
{
    public function get(Model $record): string
    {
        return Vite::asset('resources/images/profile.svg');
    }
}
