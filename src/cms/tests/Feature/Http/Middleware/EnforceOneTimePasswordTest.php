<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\EnforceOneTimePassword;
use App\Models\Organisation;
use App\Models\User;
use App\Services\OtpService;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\ConfigTestHelper;

use function expect;
use function fake;
use function it;
use function sprintf;

it('allows access when two-factor disabled', function (): void {
    $user = User::factory()->create();
    $this->be($user);

    $request = mockRequest(fake()->slug);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('allows access on logout', function (): void {
    $user = User::factory()->create();
    $this->be($user);

    $request = mockRequest(fake()->slug);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('allows access on profile', function (): void {
    $user = User::factory()->withOrganisation()->create([
        'otp_confirmed_at' => null,
    ]);
    $organisation = $user->organisations()->first();
    $organisation->update(['otp_required' => true]);
    $this->be($user);

    $slug = $organisation->slug;

    $request = mockRequest(fake()->slug, ['tenant' => $slug]);
    $request->shouldReceive('routeIs')
        ->once()
        ->andReturn('profile');

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('allows access if no tenant-id', function (): void {
    $user = User::factory()->create();
    $this->be($user);

    $request = mockRequest(fake()->slug, ['tenant' => null]);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('redirects to profile page if two-factor is not confirmed', function (): void {
    $user = User::factory()->withOrganisation()->create([
        'otp_confirmed_at' => null,
    ]);
    $organisation = $user->organisations()->first();
    $organisation->update(['otp_required' => true]);
    $this->be($user);

    $slug = $organisation->slug;

    $request = mockRequest(sprintf('%s/%s', $slug, fake()->slug()), ['tenant' => $slug]);
    $request->shouldReceive('routeIs')
        ->once()
        ->with('filament.admin.pages.profile')
        ->andReturn(false);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())
        ->toBe(302)
        ->and($response->isRedirect(sprintf('%s/%s/profile', ConfigTestHelper::get('app.url'), $slug)))
        ->toBeTrue();
});

it('redirects to two-factor page if no valid session', function (): void {
    $user = User::factory()
        ->withOrganisation()
        ->withValidOtpRegistration()
        ->create();
    $organisation = $user->organisations()->first();
    $organisation->update(['otp_required' => true]);
    $this->be($user);

    $slug = $organisation->slug;

    $request = mockRequest(sprintf('%s/%s', $slug, fake()->slug()), ['tenant' => $slug]);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())
        ->toBe(302)
        ->and($response->isRedirect(sprintf('%s/%s/two-factor-authentication?next=%%2F', ConfigTestHelper::get('app.url'), $slug)))
        ->toBeTrue();
});

it('allows access when the tenant does not require OTP (default)', function (): void {
    // otp_required defaults to false — an unconfirmed user should still
    // pass through the middleware without a redirect.
    $user = User::factory()->withOrganisation()->create([
        'otp_confirmed_at' => null,
    ]);
    $this->be($user);

    $slug = $user->organisations()->first()->slug;

    $request = mockRequest(sprintf('%s/%s', $slug, fake()->slug()), ['tenant' => $slug]);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('allows access when the tenant slug does not match any organisation', function (): void {
    // If tenant routing dumps a slug we can't resolve, fall through and
    // let downstream handlers decide (typically a 404). No 2FA redirect
    // loop.
    $user = User::factory()->create();
    $this->be($user);

    $request = mockRequest(fake()->slug, ['tenant' => 'this-slug-does-not-exist']);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('allows access when OTP is confirmed and the session is valid', function (): void {
    // Opt-in tenant + user with a valid OTP session should fall through to
    // $next without a redirect — this is the happy path once step-up is done.
    $this->mock(OtpService::class)
        ->shouldReceive('hasOtpConfirmed')
        ->once()
        ->andReturn(true)
        ->shouldReceive('hasValidSession')
        ->once()
        ->andReturn(true);

    $user = User::factory()
        ->withOrganisation()
        ->withValidOtpRegistration()
        ->create();
    $organisation = $user->organisations()->first();
    $organisation->update(['otp_required' => true]);
    $this->be($user);

    $slug = $organisation->slug;

    $request = mockRequest(sprintf('%s/%s', $slug, fake()->slug()), ['tenant' => $slug]);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(200);
});

it('enforces OTP when the organisation opts in', function (): void {
    // Sanity check that flipping the flag on an existing org — the
    // primary intended use — actually enforces the middleware.
    $organisation = Organisation::factory()->create(['otp_required' => true]);
    $user = User::factory()->create(['otp_confirmed_at' => null]);
    $user->organisations()->attach($organisation);
    $this->be($user);

    $slug = $organisation->slug;

    $request = mockRequest(sprintf('%s/%s', $slug, fake()->slug()), ['tenant' => $slug]);
    $request->shouldReceive('routeIs')
        ->once()
        ->with('filament.admin.pages.profile')
        ->andReturn(false);

    $middleware = new EnforceOneTimePassword();
    $response = $middleware->handle($request, function (): Response {
        return new Response('OK');
    });

    expect($response->getStatusCode())->toBe(302);
});
