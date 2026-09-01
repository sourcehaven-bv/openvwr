<?php

declare(strict_types=1);

namespace App\Filament\Pages\Manual;

use App\Manual\Manual;
use Filament\Pages\Page;

/**
 * A page of the manual that shows one thing: a single task or a single topic.
 *
 * Split off from the landing page because the two detail pages are identical in
 * everything except what they look up: they take one segment from the url, ask
 * the manual for it, and 404 when the manual does not have it.
 */
abstract class ManualDetailPage extends Page
{
    use ManualPage;

    protected static bool $shouldRegisterNavigation = false;

    final public function boot(Manual $manual): void
    {
        $this->bootManualPage($manual);
    }
}
