<?php

declare(strict_types=1);

namespace App\Filament\PHPStanFixture;

use App\Filament\TenantScoped;
use App\Models\Tag;

Tag::query();
Tag::where('name', 'unsafe');
Tag::whereNull('name');
// first*/update* creators read before they write, so an unscoped call here can
// match another tenant's row instead of creating one for the current tenant.
Tag::firstOrCreate(['name' => 'unsafe']);
Tag::firstOrNew(['name' => 'unsafe']);
Tag::updateOrCreate(['name' => 'unsafe'], ['name' => 'unsafe']);
Tag::tenantQuery()->where('name', 'safe')->get();

// These calls exercise the rule's safe early-return paths.
$model = Tag::class;
$model::query();
UnknownModel::query();
TenantScoped::query();
