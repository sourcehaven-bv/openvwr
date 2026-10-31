<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Config\Config;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function redirect;

/**
 * Where the panel's logout goes under the pratique driver.
 *
 * The proxy owns the session. Filament's own logout only clears this app's side
 * and then redirects to the panel, where the proxy signs the user straight back
 * in. Handing over to the proxy's logout page ends the session that matters.
 */
class PratiqueLogoutResponse implements LogoutResponse
{
    /**
     * @param Request $request
     */
    public function toResponse($request): RedirectResponse // phpcs:ignore SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
    {
        return redirect(Config::string('auth.pratique.logout_url'));
    }
}
