<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Authentication;

use App\Enums\RouteName;
use App\Models\Organisation;
use App\Models\User;
use App\Models\UserLoginToken;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

use function it;

it('shows a dutch notification when the login link has expired', function (): void {
    $token = Str::uuid()->toString();
    $user = User::factory()->create();
    $user->organisations()->attach(Organisation::factory()->create());

    UserLoginToken::factory()->for($user)->create([
        'token' => $token,
        'expires_at' => CarbonImmutable::now()->subDay(),
    ]);

    $this->get(URL::signedRoute(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONSUME, ['token' => $token]))
        ->assertRedirect('/');

    Notification::assertNotified('Deze loginlink is verlopen. Vraag een nieuwe login-e-mail aan om in te loggen.');
});

it('shows a dutch notification when the login link has no token', function (): void {
    $this->get(URL::signedRoute(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONSUME))
        ->assertRedirect('/');

    Notification::assertNotified('Deze loginlink is niet geldig. Vraag een nieuwe login-e-mail aan om in te loggen.');
});

it('shows a dutch notification when the user has no organisation', function (): void {
    $token = Str::uuid()->toString();
    $user = User::factory()->create();

    UserLoginToken::factory()->for($user)->create([
        'token' => $token,
        'expires_at' => CarbonImmutable::now()->addDay(),
    ]);

    $this->get(URL::signedRoute(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONSUME, ['token' => $token]))
        ->assertRedirect('/');

    Notification::assertNotified(
        'Uw account is nog niet aan een organisatie gekoppeld. Neem contact op met uw beheerder.',
    );
});
