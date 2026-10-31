<?php

declare(strict_types=1);

use App\Livewire\User\Profile\OneTimePassword;
use App\Services\OtpService;
use Tests\Helpers\Model\UserTestHelper;

it('can mount the component', function (): void {
    $user = UserTestHelper::create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->assertSee(__('user.profile.one_time_password.title'));
});

it('can enable', function (): void {
    $user = UserTestHelper::create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->callAction('enable')
        ->assertNotified(__('user.profile.one_time_password.enabled.notify'));
});

it('can disable', function (): void {
    $user = UserTestHelper::create();

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->callAction('disable')
        ->assertNotified(__('user.profile.one_time_password.disabling.notify'));
});

it('can confirm', function (): void {
    $user = UserTestHelper::create();

    $this->mock(OtpService::class)
        ->shouldReceive('hasOtpEnabled')
        ->atLeast()->once()
        ->andReturn(true)
        ->shouldReceive('hasOtpConfirmed')
        ->atLeast()->once()
        ->andReturn(true)
        ->shouldReceive('verifyCode')
        ->once()
        ->andReturn(true);

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->callAction('confirm', [
            'code' => fake()->word(),
        ])
        ->assertNotified(__('user.profile.one_time_password.confirmation.success_notification'));
});

it('cannot confirm with an invalid code', function (): void {
    $user = UserTestHelper::create();

    $this->mock(OtpService::class)
        ->shouldReceive('hasOtpEnabled')
        ->atLeast()->once()
        ->andReturn(true)
        ->shouldReceive('hasOtpConfirmed')
        ->atLeast()->once()
        ->andReturn(true)
        ->shouldReceive('verifyCode')
        ->once()
        ->andReturn(false);

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->callAction('confirm', [
            'code' => fake()->word(),
        ])
        ->assertNotNotified(__('user.profile.one_time_password.confirmation.success_notification'));
});

it('can regenerate codes', function (): void {
    $user = UserTestHelper::create();

    $this->mock(OtpService::class)
        ->shouldReceive('hasOtpEnabled')
        ->atLeast()->once()
        ->andReturn(true)
        ->shouldReceive('hasOtpConfirmed')
        ->atLeast()->once()
        ->andReturn(false)
        ->shouldReceive('generateQRCOdeInline')
        ->atLeast()->once()
        ->andReturn('<img>')
        ->shouldReceive('disable')
        ->once()
        ->shouldReceive('enable')
        ->once();

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->callAction('regenerateCodes')
        ->assertNotified(__('user.profile.one_time_password.regenerate_codes.notify'));
});

it('can get the qr code', function (): void {
    $image = fake()->uuid();

    $user = UserTestHelper::create();

    $this->mock(OtpService::class)
        ->shouldReceive('hasOtpEnabled')
        ->times(2)
        ->andReturn(true)
        ->shouldReceive('hasOtpConfirmed')
        ->times(2)
        ->andReturn(true)
        ->shouldReceive('generateQRCodeInline')
        ->once()
        ->andReturn($image);

    $this->asFilamentUser($user)
        ->createLivewireTestable(OneTimePassword::class, [
            'user' => $user,
        ])
        ->call('getQrCode')
        ->assertReturned($image);
});
