<?php

declare(strict_types=1);

namespace App\Filament\PHPStanFixture;

use App\Filament\TenantScoped;
use App\Models\Tag;

Tag::query();
Tag::where('name', 'unsafe');
Tag::whereNull('name');
Tag::tenantQuery()->where('name', 'safe')->get();

// These calls exercise the rule's safe early-return paths.
$model = Tag::class;
$model::query();
UnknownModel::query();
TenantScoped::query();
