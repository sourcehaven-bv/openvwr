<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Config\Config;
use App\Livewire\User\Profile\OneTimePassword;
use App\Livewire\User\Profile\PersonalInfo;
use App\Livewire\User\Profile\Settings;
use App\Services\Authentication\AuthenticationStrategyFactory;
use App\Services\AuthenticationService;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Panel;
use Illuminate\Contracts\View\View;
use InvalidArgumentException;

use function __;
use function abort;

class Profile extends Page
{
    protected static ?string $slug = 'my-profile';
    protected string $view = 'filament.pages.my-profile';
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

    public static function getSlug(?Panel $panel = null): string
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

        // OTP enrolment belongs to the builtin strategy. Under the dev driver the
        // OTP gate is not in the middleware stack, so offering enrolment here
        // would let a developer set up a factor that is never challenged.
        if (Config::string('auth.driver', AuthenticationStrategyFactory::DRIVER_BUILTIN) !== AuthenticationStrategyFactory::DRIVER_DEV) {
            $components['one_time_password'] = OneTimePassword::class;
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
