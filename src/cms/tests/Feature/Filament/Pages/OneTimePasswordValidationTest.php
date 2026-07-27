<?php

declare(strict_types=1);

use App\Filament\Pages\OneTimePasswordValidation;
use App\Services\OtpService;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Helpers\ConfigTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\SessionTestHelper;

it('loads the page', function (): void {
    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation);

    SessionTestHelper::setOtpInvalid();

    $this->get(sprintf('%s/two-factor-authentication', $organisation->slug))
        ->assertSee(__('user.one_time_password.description'));
});

it('redirects user to home on valid session', function (): void {
    $organisation = OrganisationTestHelper::create();

    $this->asFilamentOrganisationUser($organisation)
        ->get(sprintf('%s/two-factor-authentication', $organisation->slug))
        ->assertRedirect($organisation->slug);
});

it('redirects with a valid code', function (): void {
    $this->mock(OtpService::class)
        ->shouldReceive('hasValidSession')
        ->andReturn(false)
        ->shouldReceive('verifyCode')
        ->once()
        ->andReturn(true);

    $organisation = OrganisationTestHelper::create();
    $this->asFilamentOrganisationUser($organisation)
        ->createLivewireTestable(OneTimePasswordValidation::class)
        ->fillForm([
            'code' => fake()->unique()->randomNumber(6),
        ])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect($organisation->slug);
});

it('fails with a invalid code', function (): void {
    $this->asFilamentUser();

    SessionTestHelper::setOtpInvalid();

    $this->createLivewireTestable(OneTimePasswordValidation::class)
        ->fillForm([
            'code' => fake()->unique()->randomNumber(6),
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['code' => __('user.profile.one_time_password.confirmation.invalid_code')]);
});

it('fails if rate limit reached', function (): void {
    ConfigTestHelper::set('auth.one_time_password.validation_rate_limit.max_attempts', 1);
    ConfigTestHelper::set('auth.one_time_password.validation_rate_limit.decay_in_seconds', 60);

    RateLimiter::shouldReceive('availableIn')
        ->once()
        ->andReturn(60);
    RateLimiter::shouldReceive('tooManyAttempts')
        ->once()
        ->andReturn(true);

    $this->asFilamentUser();
    SessionTestHelper::setOtpInvalid();

    $this->createLivewireTestable(OneTimePasswordValidation::class)
        ->fillForm([
            'code' => fake()->unique()->randomNumber(6),
        ])
        ->call('authenticate')
        ->assertHasFormErrors(['code' => __('user.profile.one_time_password.confirmation.too_many_requests', ['seconds' => 60])]);
});

it('fails if no user', function (): void {
    $this->createLivewireTestable(OneTimePasswordValidation::class)
        ->assertRedirect('logout');
});
