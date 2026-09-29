<?php

declare(strict_types=1);

namespace App\Filament\Actions\OneTimePassword;

use App\Facades\Otp;
use App\Livewire\User\Profile\OneTimePassword;
use App\Models\User;
use App\ValueObjects\OneTimePassword\Code;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Webmozart\Assert\Assert;

use function __;

class ConfirmAction extends Action
{
    public static function makeForUser(User $user): static
    {
        return parent::make('confirm')
            ->color('success')
            ->label(__('user.profile.one_time_password.actions.confirm_finish'))
            ->modalWidth('sm')
            ->schema([
                TextInput::make('code')
                    ->label(__('user.profile.one_time_password.code'))
                    ->autofocus()
                    ->required(),
            ])
            ->action(static function (array $data, ConfirmAction $action, OneTimePassword $livewire) use ($user): void {
                $code = $data['code'];
                Assert::string($code);

                $isValid = Otp::verifyCode(Code::fromString($code), $user);
                if (!$isValid) {
                    $livewire->addError(
                        'mountedActionsData.0.code',
                        __('user.profile.one_time_password.confirmation.invalid_code'),
                    );
                    $action->halt();
                }

                Notification::make()
                    ->success()
                    ->title(__('user.profile.one_time_password.confirmation.success_notification'))
                    ->send();
            });
    }
}
