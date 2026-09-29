<?php

declare(strict_types=1);

it('can run a schedule', function (): void {
    $this->artisan('schedule:test --name user:delete-expired-login-tokens')
        ->assertSuccessful();
});

it('schedules the cleanup of deleted records', function (): void {
    $this->artisan('schedule:test --name cleanup:soft-deleted')
        ->assertSuccessful();
});
