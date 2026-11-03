<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Config\Config;
use Illuminate\Contracts\View\View;
use Livewire\Component;

use function view;

/**
 * Under the pratique driver the proxy owns sign-in. Passkeys, login codes and
 * sessions are managed on its account page, so the profile links there.
 */
class PratiqueAccount extends Component
{
    public function render(): View
    {
        return view('livewire.user.profile.pratique-account', [
            'accountUrl' => Config::string('auth.pratique.account_url'),
        ]);
    }
}
