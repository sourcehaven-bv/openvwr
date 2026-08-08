<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use App\Services\Authentication\AuthenticationStrategyFactory;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Pages\Auth\Login as FilamentLogin;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function session;
use function sprintf;

/**
 * Credential-free login for local development: pick a user, press the button.
 *
 * Exists so the app has two working auth strategies from the moment the seam is
 * introduced — an interface with a single implementation proves nothing. It also
 * makes local work considerably less tedious: no mail round-trip, no authenticator
 * app, and switching between the eight roles is a dropdown.
 *
 * SAFETY: this bypasses authentication entirely. It is only registered when the
 * dev driver is active (see FilamentServiceProvider), the driver itself refuses
 * to build outside local/testing (AuthenticationStrategyFactory), and the guard
 * below is a third, independent check at the point of use.
 */
class DevLogin extends FilamentLogin
{
    /**
     * Filament discovers every class under app/Filament/Pages and registers it as
     * a Livewire component, regardless of which login page the panel is using. So
     * this component is addressable in production even though the panel never
     * links to it — which means mounting it must be refused before anything else
     * runs, not just before it authenticates. Without this, rendering the form
     * would list every user's name and email to an unauthenticated caller.
     */
    public function mount(): void
    {
        $this->assertDevLoginAllowed();

        parent::mount();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('userId')
                ->label(__('auth.dev_login_user'))
                ->options(self::userOptions())
                ->searchable()
                ->required(),
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $this->assertDevLoginAllowed();

        $data = $this->form->getState();
        $userId = $data['userId'];
        Assert::string($userId);

        // The option list is presentation only — Livewire takes the submitted
        // value from the client. Re-check membership here so a crafted request
        // cannot log in as a user the picker never offered (one without an
        // organisation would 403 on arrival anyway, but this keeps the rule in
        // one place rather than relying on the select).
        $user = User::query()
            ->has('organisations')
            ->findOrFail($userId);

        Auth::login($user);
        session()->regenerate();

        return app(LoginResponseContract::class);
    }

    public function getHeading(): string
    {
        return __('auth.dev_login_heading');
    }

    /**
     * Users that can actually be logged in as. Mirrors the builtin strategy's
     * rule that a user without an organisation cannot reach the panel, so the
     * picker never offers an account that would 403 on arrival.
     *
     * @return array<string, string>
     */
    private static function userOptions(): array
    {
        $users = User::query()
            ->has('organisations')
            ->get()
            ->sortBy('name');

        return $users
            ->mapWithKeys(static fn (User $user): array => [
                $user->id->toString() => sprintf('%s (%s)', $user->name, $user->email),
            ])
            ->all();
    }

    /**
     * Defence in depth: the factory already refuses to build the dev strategy
     * outside local/testing, but this page must not authenticate anyone if it is
     * ever reached by another route.
     *
     * @throws RuntimeException
     */
    private function assertDevLoginAllowed(): void
    {
        $environment = app()->environment();

        if (!AuthenticationStrategyFactory::devAllowedIn($environment)) {
            throw new RuntimeException(
                sprintf('Dev login is not available in the "%s" environment.', $environment),
            );
        }
    }
}
