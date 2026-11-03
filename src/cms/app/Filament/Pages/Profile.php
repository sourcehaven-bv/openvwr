<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Config\Config;
use App\Livewire\User\Profile\OneTimePassword;
use App\Livewire\User\Profile\PersonalInfo;
use App\Livewire\User\Profile\PratiqueAccount;
use App\Livewire\User\Profile\Settings;
use App\Services\Authentication\AuthenticationStrategyFactory;
use App\Services\AuthenticationService;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;

use function __;
use function abort;

class Profile extends Page
{
    protected static ?string $slug = 'my-profile';
    protected static string $view = 'filament.pages.my-profile';
    private AuthenticationService $authenticationService;

    public function boot(AuthenticationService $authenticationService): void
    {
        $this->authenticationService = $authenticationService;
    }

    public function getTitle(): string
    {
        return __('user.profile.my_profile');
    }

    public function getHeading(): string
    {
        return __('user.profile.my_profile');
    }

    public static function getLabel(): string
    {
        return __('user.profile.my_profile');
    }

    public static function getSlug(): string
    {
        return 'profile';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * @return array<string, class-string>
     */
    public function getRegisteredMyProfileComponents(): array
    {
        $components = [
            'personal_info' => PersonalInfo::class,
            'settings' => Settings::class,
        ];

        $driver = Config::string('auth.driver', AuthenticationStrategyFactory::DRIVER_BUILTIN);

        // OTP enrolment belongs to the builtin strategy. Under the dev and
        // pratique drivers the OTP gate is not in the middleware stack, so a
        // factor set up here would never be challenged.
        if ($driver === AuthenticationStrategyFactory::DRIVER_BUILTIN) {
            $components['one_time_password'] = OneTimePassword::class;
        }

        if ($driver === AuthenticationStrategyFactory::DRIVER_PRATIQUE) {
            $components['pratique_account'] = PratiqueAccount::class;
        }

        return $components;
    }

    public function render(): View
    {
        try {
            $this->authenticationService->organisation();
        } catch (InvalidArgumentException) {
            abort(404);
        }

        return parent::render();
    }
}
