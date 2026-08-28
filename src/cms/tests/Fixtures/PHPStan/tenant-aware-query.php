<?php

declare(strict_types=1);

namespace App\Filament\PHPStanFixture;

use App\Models\Tag;

Tag::query();
Tag::where('name', 'unsafe');
Tag::whereNull('name');
Tag::tenantQuery()->where('name', 'safe')->get();
