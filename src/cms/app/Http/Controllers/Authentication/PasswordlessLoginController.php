<?php

declare(strict_types=1);

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;
use App\Models\UserLoginToken;
use App\Services\AuditLog\AuditLogger;
use App\Services\AuditLog\Authentication\AuthenticationFailedEvent;
use App\Services\AuditLog\Authentication\AuthenticationSuccessEvent;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\Factory;

use function auth;
use function is_string;
use function redirect;

class PasswordlessLoginController extends Controller
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly Factory $view,
    ) {
    }

    public function consume(Request $request): RedirectResponse|View
    {
        try {
            $this->getValidUserLoginToken($request);
        } catch (PasswordlessLoginException $exception) {
            Notification::make()
                ->title($exception->getTranslatedMessage())
                ->danger()
                ->send();

            auth()->logout();

            return redirect('/');
        }

        return $this->view->make('auth.consume', [
            'postUrl' => $request->getRequestUri(),
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        try {
            $userLoginToken = $this->getValidUserLoginToken($request);
        } catch (PasswordlessLoginException $exception) {
            Notification::make()
                ->title($exception->getTranslatedMessage())
                ->danger()
                ->send();

            auth()->logout();

            return redirect('/');
        }

        $user = $userLoginToken->user;
        $user->userLoginTokens()->truncate();

        auth()->login($user);
        $this->auditLogger->register(new AuthenticationSuccessEvent($user));

        return redirect($userLoginToken->destination ?? '/');
    }

    /**
     * @throws PasswordlessLoginException
     */
    private function getValidUserLoginToken(Request $request): UserLoginToken
    {
        $token = $request->query('token');

        if (!is_string($token)) {
            $this->auditLogger->register(AuthenticationFailedEvent::invalidToken());
            throw PasswordlessLoginException::invalidToken();
        }

        $userLoginToken = UserLoginToken::where(['token' => $token])
            ->where('expires_at', '>=', CarbonImmutable::now())
            ->first();

        if ($userLoginToken === null) {
            $this->auditLogger->register(AuthenticationFailedEvent::noTokenFound());
            throw PasswordlessLoginException::noTokenFound();
        }

        $user = $userLoginToken->user;
        if ($user->organisations->isEmpty()) {
            $this->auditLogger->register(AuthenticationFailedEvent::noOrganisationFound());
            throw PasswordlessLoginException::noOrganisationFound();
        }

        return $userLoginToken;
    }
}
