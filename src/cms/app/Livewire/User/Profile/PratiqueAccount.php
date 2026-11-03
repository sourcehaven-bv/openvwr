<?php

declare(strict_types=1);

namespace App\Livewire\User\Profile;

use App\Config\Config;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Livewire\Attributes\Locked;
use Livewire\Component;

use function http_build_query;
use function str_contains;
use function view;

/**
 * Under the pratique driver the proxy owns sign-in. Passkeys, login codes and
 * sessions are managed on its account page, so the profile links there. The
 * link passes this page as rd, so the account page's back link returns here.
 */
class PratiqueAccount extends Component
{
    /**
     * Captured on the first render: later Livewire updates run on the update
     * endpoint, not on the profile page.
     */
    #[Locked]
    public string $returnTo = '/';

    public function mount(Request $request): void
    {
        $this->returnTo = $request->getRequestUri();
    }

    public function render(): View
    {
        $accountUrl = Config::string('auth.pratique.account_url');
        $separator = str_contains($accountUrl, '?') ? '&' : '?';

        return view('livewire.user.profile.pratique-account', [
            'accountUrl' => $accountUrl . $separator . http_build_query(['rd' => $this->returnTo]),
        ]);
    }
}
