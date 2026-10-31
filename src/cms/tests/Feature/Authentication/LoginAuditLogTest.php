<?php

declare(strict_types=1);

use App\Enums\RouteName;
use App\Facades\Otp;
use App\Filament\Pages\OneTimePasswordValidation;
use App\Models\Organisation;
use App\Models\User;
use App\Models\UserLoginToken;
use App\Services\AuditLog\Authentication\AuthenticationFailedEvent;
use App\Services\AuditLog\Authentication\TwoFactorFailedEvent;
use App\ValueObjects\OneTimePassword\Code;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use MinVWS\AuditLogger\Events\Logging\UserLoginLogEvent;
use MinVWS\AuditLogger\Events\Logging\UserLoginTwoFactorFailedEvent;
use MinVWS\AuditLogger\Events\Logging\UserLogoutLogEvent;
use OTPHP\TOTP;
use Tests\Helpers\AuditLogTestHelper;
use Tests\Helpers\Model\OrganisationTestHelper;
use Tests\Helpers\Model\UserTestHelper;

/**
 * First factor: request a login link and follow it.
 */
function loginWithLink(User $user): void
{
    $token = Str::uuid()->toString();
    UserLoginToken::factory()->for($user)->create([
        'token' => $token,
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    test()->post(URL::signedRoute(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONFIRM, ['token' => $token]))
        ->assertRedirect();
    expect(auth()->id())->toBe($user->id);
}

function validCode(User $user): string
{
    $secret = $user->otp_secret;
    assert(is_string($secret));

    return TOTP::createFromSecret($secret)->now();
}

function submitCode(Organisation $organisation, string $code): void
{
    test()->get(sprintf('%s/two-factor-authentication', $organisation->slug))
        ->assertOk();

    test()->createLivewireTestable(OneTimePasswordValidation::class)
        ->fillForm(['code' => $code])
        ->call('authenticate');
}

/**
 * @return list<array<string, mixed>>
 */
function auditLogs(string $eventCode): array
{
    return test()->auditLog->records($eventCode);
}

beforeEach(function (): void {
    $this->auditLog = AuditLogTestHelper::capture();
    $this->organisation = OrganisationTestHelper::create();
    $this->user = UserTestHelper::createForOrganisation($this->organisation);
});

it('records a login only after the one-time password', function (): void {
    loginWithLink($this->user);

    expect(auditLogs(UserLoginLogEvent::EVENT_CODE))->toBeEmpty();

    $this->get($this->organisation->slug)
        ->assertRedirectContains(sprintf('%s/two-factor-authentication', $this->organisation->slug));

    $code = validCode($this->user);
    submitCode($this->organisation, $code);

    $logs = auditLogs(UserLoginLogEvent::EVENT_CODE);
    expect($logs)->toHaveCount(1)
        ->and($logs[0]['user_id'])->toBe($this->user->id->toString())
        ->and($logs[0]['failed'])->toBeFalse();
});

it('records a failed one-time password', function (): void {
    loginWithLink($this->user);

    $code = validCode($this->user) === '000000' ? '111111' : '000000';
    submitCode($this->organisation, $code);

    $logs = auditLogs(UserLoginTwoFactorFailedEvent::EVENT_CODE);
    expect($logs)->toHaveCount(1)
        ->and($logs[0]['user_id'])->toBe($this->user->id->toString())
        ->and($logs[0]['failed'])->toBeTrue()
        ->and($logs[0]['failed_reason'])->toBe(TwoFactorFailedEvent::INVALID_CODE)
        ->and(auditLogs(UserLoginLogEvent::EVENT_CODE))->toBeEmpty();
});

it('records a rate-limited one-time password attempt', function (): void {
    loginWithLink($this->user);

    RateLimiter::shouldReceive('tooManyAttempts')->andReturn(true);
    RateLimiter::shouldReceive('availableIn')->andReturn(60);

    $this->createLivewireTestable(OneTimePasswordValidation::class)
        ->fillForm(['code' => validCode($this->user)])
        ->call('authenticate');

    $logs = auditLogs(UserLoginTwoFactorFailedEvent::EVENT_CODE);
    expect($logs)->toHaveCount(1)
        ->and($logs[0]['user_id'])->toBe($this->user->id->toString())
        ->and($logs[0]['failed_reason'])->toBe(TwoFactorFailedEvent::TOO_MANY_ATTEMPTS)
        ->and(auditLogs(UserLoginLogEvent::EVENT_CODE))->toBeEmpty();
});

it('records a failed login link', function (): void {
    $this->post(URL::signedRoute(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONFIRM, ['token' => Str::uuid()->toString()]))
        ->assertRedirect('/');

    $logs = array_values(array_filter($this->auditLog->records(), static fn (array $log): bool => $log['failed']));
    expect($logs)->toHaveCount(1)
        ->and($logs[0]['failed_reason'])->toBe(AuthenticationFailedEvent::NO_TOKEN_FOUND)
        ->and($logs[0]['user_id'])->toBeNull()
        ->and(auditLogs(UserLogoutLogEvent::EVENT_CODE))->toBeEmpty();
});

it('records a logout', function (): void {
    loginWithLink($this->user);
    submitCode($this->organisation, validCode($this->user));

    $this->post(route('filament.admin.auth.logout'))
        ->assertRedirect();
    $this->assertGuest();

    $logs = auditLogs(UserLogoutLogEvent::EVENT_CODE);
    expect($logs)->toHaveCount(1)
        ->and($logs[0]['user_id'])->toBe($this->user->id->toString());
});

it('does not record a login when a verified session confirms a code again', function (): void {
    loginWithLink($this->user);
    submitCode($this->organisation, validCode($this->user));

    // As the profile does when a user re-enrols their authenticator.
    expect(Otp::verifyCode(Code::fromString(validCode($this->user)), $this->user))->toBeTrue()
        ->and(auditLogs(UserLoginLogEvent::EVENT_CODE))->toHaveCount(1);
});

it('records no personal data or codes', function (): void {
    loginWithLink($this->user);
    $code = validCode($this->user);
    submitCode($this->organisation, $code);
    $this->post(route('filament.admin.auth.logout'));

    expect($this->auditLog->lines())->not->toBeEmpty();
    foreach ($this->auditLog->lines() as $line) {
        expect($line)
            ->not->toContain($this->user->email)
            ->not->toContain($this->user->name)
            ->not->toContain($code);
    }
});
