<?php

declare(strict_types=1);

use App\Models\EntityNumberCounter;
use App\Models\Organisation;
use App\Models\User;

it('can make an admin user', function (): void {
    $name = fake()->userName();
    $email = fake()->safeEmail();
    $organisationName = fake()->company();

    $this->artisan('user:create-admin')
        ->expectsQuestion('Name', $name)
        ->expectsQuestion('Email address', $email)
        ->expectsQuestion('Organisation', $organisationName)
        ->assertSuccessful();

    $user = User::query()
        ->where('name', $name)
        ->where('email', $email)
        ->firstOrFail();

    $organisation = Organisation::query()
        ->where('name', $organisationName)
        ->where('allowed_ips', "*.*.*.*")
        ->firstOrFail();

    expect($user->organisations()->first()->id)
        ->toBe($organisation->id);
});

it('can make an admin user for an existing organisation', function (): void {
    $name = fake()->userName();
    $email = fake()->safeEmail();
    $organisationName = fake()->slug();

    $organisation = Organisation::factory()->create([
        'slug' => $organisationName,
    ]);

    $this->artisan('user:create-admin')
        ->expectsQuestion('Name', $name)
        ->expectsQuestion('Email address', $email)
        ->expectsQuestion('Organisation', $organisationName)
        ->assertSuccessful();

    $user = User::query()
        ->where('name', $name)
        ->where('email', $email)
        ->firstOrFail();

    expect($user->organisations()->first()->id)
        ->toBe($organisation->id);
});

it('handles an error correctly', function (): void {
    $prefix = fake()->randomLetter();
    EntityNumberCounter::factory()->create([
        'prefix' => $prefix,
    ]);

    $organisation = Organisation::factory()->create([
        'name' => $prefix,
    ]);

    $this->artisan('user:create-admin')
        ->expectsQuestion('Name', fake()->userName())
        ->expectsQuestion('Email address', fake()->safeEmail())
        ->expectsQuestion('Organisation', $organisation->name)
        ->assertFailed();
});

it('can make an admin user from CLI flags (non-interactive)', function (): void {
    $name = fake()->userName();
    $email = fake()->safeEmail();
    $organisationName = fake()->company();

    $this->artisan('user:create-admin', [
        '--name' => $name,
        '--email' => $email,
        '--organisation' => $organisationName,
    ])
        ->assertSuccessful();

    $user = User::query()
        ->where('name', $name)
        ->where('email', $email)
        ->firstOrFail();

    $organisation = Organisation::query()
        ->where('name', $organisationName)
        ->firstOrFail();

    expect($user->organisations()->first()->id)
        ->toBe($organisation->id);
});

it('rejects invalid email in non-interactive mode', function (): void {
    $this->artisan('user:create-admin', [
        '--name' => 'Admin',
        '--email' => 'not-an-email',
        '--organisation' => fake()->company(),
    ])
        ->assertFailed();
});

it('rejects duplicate email in non-interactive mode', function (): void {
    $existing = User::factory()->create();

    $this->artisan('user:create-admin', [
        '--name' => 'Admin',
        '--email' => $existing->email,
        '--organisation' => fake()->company(),
    ])
        ->assertFailed();
});

it('allows mixing flags with prompts (partial CLI input)', function (): void {
    // Only --email passed; name + organisation still prompted. Lets an
    // operator on a shell pre-fill a value from clipboard without
    // giving up the prompt safety net.
    $name = fake()->userName();
    $email = fake()->safeEmail();
    $organisationName = fake()->company();

    $this->artisan('user:create-admin', ['--email' => $email])
        ->expectsQuestion('Name', $name)
        ->expectsQuestion('Organisation', $organisationName)
        ->assertSuccessful();

    User::query()
        ->where('name', $name)
        ->where('email', $email)
        ->firstOrFail();
});
