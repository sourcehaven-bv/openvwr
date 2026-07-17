<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Config\Config;
use App\Facades\Otp;
use App\Services\AuthenticationService;
use App\ValueObjects\OneTimePassword\Code;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Illuminate\Http\RedirectResponse;
use Livewire\Attributes\Url;
use Livewire\Features\SupportRedirects\Redirector;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

use function __;
use function array_merge;
use function redirect;

class OneTimePasswordValidation extends SimplePage
{
    use InteractsWithFormActions;
    use WithRateLimiting;

    private AuthenticationService $authenticationService;
    protected static string $view = 'filament.pages.one-time-password-validation';
    public ?string $code;

    #[Url]
    public ?string $next;

    protected function getLayoutData(): array
    {
        return array_merge(parent::getLayoutData(), [
            'hasDatabaseNotifications' => false,
        ]);
    }

    public function getTitle(): string
    {
        return __('user.one_time_password.heading');
    }

    public function getSubheading(): string
    {
        return __('user.one_time_password.description');
    }

    public function boot(AuthenticationService $authenticationService): void
    {
        $this->authenticationService = $authenticationService;
    }

    public function mount(): void
    {
        try {
            $this->authenticationService->user();
        } catch (InvalidArgumentException) {
            redirect()->to(Filament::getLogoutUrl());
        }

        if (!Otp::hasValidSession()) {
            return;
        }

        $homeUrl = Filament::getHomeUrl();
        Assert::string($homeUrl);

        redirect()->to($homeUrl);
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('code')
                ->label(__('user.one_time_password.code'))
                ->required()
                ->extraInputAttributes(['class' => 'text-center', 'autocomplete' => 'one-time-code'])
                ->autofocus(),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    public function authenticate(): Redirector|RedirectResponse|null
    {
        try {
            $this->rateLimit(
                Config::integer('auth.one_time_password.validation_rate_limit.max_attempts'),
                Config::integer('auth.one_time_password.validation_rate_limit.decay_in_seconds'),
            );
        } catch (TooManyRequestsException $exception) {
            $secondsUntilAvailable = $exception->secondsUntilAvailable;
            Assert::integer($secondsUntilAvailable);

            $this->addError('code', __('user.profile.one_time_password.confirmation.too_many_requests', [
                'seconds' => $secondsUntilAvailable,
            ]));

            return null;
        }

        if (!$this->hasValidCode()) {
            $this->addError('code', __('user.profile.one_time_password.confirmation.invalid_code'));

            return null;
        }

        $path = $this->next ?? Filament::getHomeUrl();
        Assert::string($path);

        return redirect()->to($path);
    }

    private function hasValidCode(): bool
    {
        Assert::string($this->code);
        $user = $this->authenticationService->user();

        return Otp::verifyCode(Code::fromString($this->code), $user);
    }

    private function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->label(__('filament-panels::pages/auth/login.form.actions.authenticate.label'))
            ->submit('authenticate');
    }
}
