<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use App\Config\Config;
use App\Facades\AdminLog;
use App\Models\User;
use App\Services\UserLoginToken\UserLoginService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Webmozart\Assert\Assert;

use function __;
use function app;
use function ceil;

class Login extends \Filament\Auth\Pages\Login
{
    public function form(Schema $schema): Schema
    {
        return $schema->components([
            $this->getEmailFormComponent(),
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(
                Config::integer('auth.passwordless.throttle.max_attempts'),
                Config::integer('auth.passwordless.throttle.window'),
            );
        } catch (TooManyRequestsException $exception) {
            $secondsUntilAvailable = $exception->secondsUntilAvailable;
            Assert::integer($secondsUntilAvailable);

            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $secondsUntilAvailable,
                    'minutes' => ceil($secondsUntilAvailable / 60),
                ]))
                ->body(__('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $secondsUntilAvailable,
                    'minutes' => ceil($secondsUntilAvailable / 60),
                ]))
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();
        $email = $data['email'];
        Assert::string($email);

        try {
            $user = User::where(['email' => Str::lower($email)])->firstOrFail();
        } catch (ModelNotFoundException) {
            AdminLog::log('authentication failed', [
                'reason' => 'email not found',
                'email' => $email,
            ]);
            $this->sendNotification();
            return null;
        }

        if ($user->organisations->count() === 0) {
            AdminLog::log('authentication failed', [
                'reason' => 'no organisation',
                'email' => $email,
            ]);
            $this->sendNotification();
            return null;
        }

        /** @var UserLoginService $userLoginService */
        $userLoginService = app(UserLoginService::class);
        $destination = Session::get('url.intended', '/');
        Assert::string($destination);

        $userLoginService->sendPasswordLessLoginLink($user, $destination);
        $this->sendNotification();

        return null;
    }

    public function getHeading(): string
    {
        return '';
    }

    private function sendNotification(): void
    {
        Notification::make()
            ->title(__('auth.login_sent'))
            ->success()
            ->send();
    }
}
