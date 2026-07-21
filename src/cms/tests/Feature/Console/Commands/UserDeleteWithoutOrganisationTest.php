<?php

declare(strict_types=1);

use App\Models\User;

it('can run the command', function (): void {
    $user = User::factory()
        ->create();

    $this->artisan('user:delete-without-organisation')
        ->assertSuccessful();

    $user->refresh();

    expect($user->trashed())
        ->toBeTrue();
});

it('does not update deleted_at for already deleted users', function (): void {
    $deletedAt = fake()->dateTime();

    $user = User::factory()->create([
        'deleted_at' => $deletedAt,
    ]);

    $this->artisan('user:delete-without-organisation')
        ->assertSuccessful();

    $user->refresh();

    expect($user->deleted_at)->toEqual($deletedAt);
});
