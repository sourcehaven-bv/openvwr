<?php

declare(strict_types=1);

namespace Tests\Feature\Console\Commands;

use App\Models\User;

use function it;
use function json_encode;

use const JSON_THROW_ON_ERROR;

it('can list users', function (): void {
    $user = User::factory()->create();

    $this->artisan('user:list')
        ->assertOk()
        ->expectsTable(['Name', 'Email'], [[$user->name, $user->email]]);
});

it('can list users with filter', function (): void {
    $user = User::factory()->create();

    $this->artisan('user:list', ['--filter' => $user->email])
        ->assertOk()
        ->expectsTable(['Name', 'Email'], [[$user->name, $user->email]]);
});

it('can list users as json', function (): void {
    $user = User::factory()->create();

    $this->artisan('user:list', ['--json' => true])
        ->assertOk()
        ->expectsOutput(json_encode([
            [
                'name' => $user->name,
                'email' => $user->email,
            ]], JSON_THROW_ON_ERROR));
});

it('can list filtered users as json', function (): void {
    $user = User::factory()->create();
    User::factory()->create();

    $this->artisan('user:list', [
        '--filter' => $user->email,
        '--json' => true,
    ])
        ->assertOk()
        ->expectsOutput(json_encode([
            [
                'name' => $user->name,
                'email' => $user->email,
            ]], JSON_THROW_ON_ERROR));
});
