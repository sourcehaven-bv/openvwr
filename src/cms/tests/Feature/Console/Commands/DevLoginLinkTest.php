<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\App;

it('mints a login link for an existing user', function (): void {
    $user = User::factory()->create(['email' => 'demo@example.com']);

    $this->artisan('dev:login-link', ['--email' => 'demo@example.com'])
        ->assertSuccessful();

    expect($user->userLoginTokens()->count())->toBe(1);
});

it('replaces any existing login token for the user', function (): void {
    $user = User::factory()->create(['email' => 'demo@example.com']);

    $this->artisan('dev:login-link', ['--email' => 'demo@example.com'])->assertSuccessful();
    $this->artisan('dev:login-link', ['--email' => 'demo@example.com'])->assertSuccessful();

    expect($user->userLoginTokens()->count())->toBe(1);
});

it('fails when no user matches the email', function (): void {
    $this->artisan('dev:login-link', ['--email' => 'nobody@example.com'])
        ->assertFailed();
});

it('refuses to run in production', function (): void {
    App::shouldReceive('isProduction')->andReturnTrue();

    $this->artisan('dev:login-link', ['--email' => 'demo@example.com'])
        ->assertFailed();
});
