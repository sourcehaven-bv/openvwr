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

/*
 * Filament registers every class under app/Filament/Pages as a Livewire
 * component, whichever login page the panel actually uses — so this component is
 * addressable in production even though nothing links to it. Mounting must be
 * refused before the form is built, because building it queries every user and
 * renders their name and email. Guarding only authenticate() would leave the
 * whole user directory readable by an unauthenticated caller.
 */
it('refuses to render outside local and testing', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    app()->detectEnvironment(static fn (): string => 'production');

    // Livewire wraps a mount-time throw, so walk the chain: what matters is that
    // the guard fired before the form was built and no user data was rendered.
    $thrown = null;

    try {
        livewire(DevLogin::class);
    } catch (Throwable $exception) {
        $thrown = $exception;
    }

    expect($thrown)->not->toBeNull('mounting dev login in production should fail');

    $messages = [];
    for ($e = $thrown; $e !== null; $e = $e->getPrevious()) {
        $messages[] = $e->getMessage();
    }

    expect(implode(' | ', $messages))->toContain('Dev login is not available');
});

/*
 * The page's own environment guard, independent of the factory's. If dev login
 * is ever reachable in a deployed environment it must refuse to authenticate
 * anyone — so this is the single most important assertion in this file.
 */
it('refuses to authenticate anyone outside local and testing', function (): void {
    $organisation = Organisation::factory()->create();
    $user = User::factory()->create();
    $user->organisations()->attach($organisation);

    $component = livewire(DevLogin::class)
        ->fillForm(['userId' => $user->id->toString()]);

    app()->detectEnvironment(static fn (): string => 'production');

    expect(static fn () => $component->call('authenticate'))
        ->toThrow(RuntimeException::class, 'Dev login is not available');

    expect(Auth::check())->toBeFalse();
});
