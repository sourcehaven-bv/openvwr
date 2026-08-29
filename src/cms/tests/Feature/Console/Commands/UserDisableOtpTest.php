<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Console\Command;

it('can reset otp', function (): void {
    $otpSecret = fake()->regexify('[A-Z]{16}');

    $user = User::factory()
        ->create([
            'otp_secret' => $otpSecret,
        ]);

    expect($user->otp_secret)
        ->toBe($otpSecret);

    $this->artisan('user:disable-otp')
        ->expectsQuestion('Email address', $user->email)
        ->assertSuccessful();

    $user->refresh();
    expect($user->otp_secret)
        ->toBeNull();
});

it('can reset otp using an email argument', function (): void {
    $otpSecret = fake()->regexify('[A-Z]{16}');

    $user = User::factory()
        ->create([
            'otp_secret' => $otpSecret,
        ]);

    $this->artisan('user:disable-otp', [
        'email' => $user->email,
        '--no-interaction' => true,
    ])
        ->assertSuccessful();

    $user->refresh();
    expect($user->otp_secret)
        ->toBeNull();
});

it('fails with an unknown email address', function (): void {
    $this->artisan('user:disable-otp')
        ->expectsQuestion('Email address', fake()->safeEmail())
        ->assertFailed();
});

it('fails with an unknown email address argument', function (): void {
    $this->artisan('user:disable-otp', ['email' => fake()->safeEmail()])
        ->assertFailed();
});

it('fails without an email argument in non-interactive mode', function (): void {
    $otpSecret = fake()->regexify('[A-Z]{16}');

    $user = User::factory()
        ->create([
            'otp_secret' => $otpSecret,
        ]);

    $this->artisan('user:disable-otp', ['--no-interaction' => true])
        ->assertExitCode(Command::INVALID);

    $user->refresh();
    expect($user->otp_secret)
        ->toBe($otpSecret);
});
