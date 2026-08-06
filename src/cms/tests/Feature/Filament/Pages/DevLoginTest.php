<?php

declare(strict_types=1);

use App\Filament\Pages\DevLogin;
use App\Models\Organisation;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

it('logs in the selected user without any credential', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    expect(Auth::check())->toBeFalse();

    livewire(DevLogin::class)
        ->fillForm(['userId' => $user->id->toString()])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(Auth::check())->toBeTrue();

    $authenticated = Auth::user();
    expect($authenticated)->toBeInstanceOf(User::class);
    expect($authenticated->id->toString())->toBe($user->id->toString());
});

/*
 * The picker mirrors the builtin strategy's rule that a user without an
 * organisation cannot reach the panel, so it must not offer an account that
 * would 403 immediately on arrival.
 */
/*
 * A user without an organisation cannot reach the panel (the builtin strategy
 * enforces that at login, and canAccessTenant would reject them anyway), so the
 * picker must not offer an account that would 403 on arrival.
 */
it('only offers users that belong to an organisation', function (): void {
    $organisation = Organisation::factory()->create();
    $withOrganisation = User::factory()->create();
    $withOrganisation->organisations()->attach($organisation);
    $withoutOrganisation = User::factory()->create();

    // The option list is presentation only — Livewire takes the submitted value
    // from the client — so assert the server refuses an orgless user outright
    // rather than merely hiding them from the dropdown.
    expect(static function () use ($withoutOrganisation): void {
        livewire(DevLogin::class)
            ->fillForm(['userId' => $withoutOrganisation->id->toString()])
            ->call('authenticate');
    })->toThrow(ModelNotFoundException::class);

    expect(Auth::check())->toBeFalse();

    livewire(DevLogin::class)
        ->fillForm(['userId' => $withOrganisation->id->toString()])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    expect(Auth::check())->toBeTrue();
});

it('requires a user to be selected', function (): void {
    livewire(DevLogin::class)
        ->call('authenticate')
        ->assertHasFormErrors(['userId' => 'required']);

    expect(Auth::check())->toBeFalse();
});
