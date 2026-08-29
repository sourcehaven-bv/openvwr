<?php

declare(strict_types=1);

namespace App\Services\PHPStanFixture;

use App\Models\Tag;

// Cross-tenant services have their own explicit scoping requirements. This
// rule guards request-facing Filament code and deliberately leaves them alone.
Tag::query();
