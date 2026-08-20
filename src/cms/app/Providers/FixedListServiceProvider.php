<?php

declare(strict_types=1);

namespace App\Providers;

use App\FixedLists\Lists\AdequacyDecisionCountryList;
use App\FixedLists\Lists\TransferMechanismList;
use Illuminate\Support\ServiceProvider;

/**
 * Fixed lists are immutable and build their lookup index once, so they are shared. Binding them also lets a
 * test swap a list for a double without depending on the real, legally changing, contents.
 */
class FixedListServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AdequacyDecisionCountryList::class);
        $this->app->singleton(TransferMechanismList::class);
    }
}
